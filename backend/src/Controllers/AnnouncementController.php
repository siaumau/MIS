<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

/**
 * 資訊安全佈達控制器
 */
class AnnouncementController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取公告列表
     */
    public function index(): void
    {
        $request = new Request();
        $page = (int)$request->input('page', 1);
        $perPage = min((int)$request->input('per_page', 20), 100);
        $search = $request->input('search', '');
        $status = $request->input('status');
        $priority = $request->input('priority');
        $category = $request->input('category');

        $currentUser = $GLOBALS['current_user'];

        // 構建查詢條件
        $conditions = [];
        $params = [];

        if ($search) {
            $conditions[] = "(sa.title LIKE ? OR sa.content LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam]);
        }

        if ($status) {
            $conditions[] = "sa.status = ?";
            $params[] = $status;
        }

        if ($priority) {
            $conditions[] = "sa.priority = ?";
            $params[] = $priority;
        }

        if ($category) {
            $conditions[] = "sa.category = ?";
            $params[] = $category;
        }

        // 如果不是管理員，只能看到已發送的公告
        if ($currentUser['role'] !== 'admin') {
            $conditions[] = "sa.status = 'sent'";
            
            // 還要檢查目標對象
            $conditions[] = "(
                sa.target_type = 'all' OR 
                (sa.target_type = 'department' AND JSON_CONTAINS(sa.target_departments, ?)) OR
                (sa.target_type = 'users' AND JSON_CONTAINS(sa.target_users, ?))
            )";
            $params = array_merge($params, [
                json_encode($currentUser['department']),
                json_encode($currentUser['id'])
            ]);
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                sa.*,
                u.full_name as created_by_name,
                CASE 
                    WHEN sa.target_type = 'all' THEN '全體員工'
                    WHEN sa.target_type = 'department' THEN CONCAT('部門：', COALESCE(sa.target_departments, ''))
                    WHEN sa.target_type = 'users' THEN '指定用戶'
                END as target_display,
                (SELECT COUNT(*) FROM announcement_reads ar WHERE ar.announcement_id = sa.id) as read_count,
                (SELECT COUNT(*) FROM announcement_reads ar WHERE ar.announcement_id = sa.id AND ar.acknowledged_at IS NOT NULL) as ack_count
            FROM security_announcements sa
            LEFT JOIN users u ON sa.created_by = u.id
            {$whereClause}
            ORDER BY sa.priority DESC, sa.created_at DESC
        ";

        $result = $this->db->paginate($sql, $params, $page, $perPage);

        // 對於非管理員，標記已讀狀態
        if ($currentUser['role'] !== 'admin') {
            foreach ($result['data'] as &$announcement) {
                $readStatus = $this->db->fetch("
                    SELECT read_at, acknowledged_at FROM announcement_reads 
                    WHERE announcement_id = ? AND user_id = ?
                ", [$announcement['id'], $currentUser['id']]);

                $announcement['is_read'] = $readStatus ? true : false;
                $announcement['is_acknowledged'] = $readStatus && $readStatus['acknowledged_at'] ? true : false;
                $announcement['read_at'] = $readStatus['read_at'] ?? null;
                $announcement['acknowledged_at'] = $readStatus['acknowledged_at'] ?? null;
            }
        }

        Response::paginated($result);
    }

    /**
     * 獲取單一公告詳細資訊
     */
    public function show(array $params): void
    {
        $id = (int)$params['id'];
        $currentUser = $GLOBALS['current_user'];

        $announcement = $this->db->fetch("
            SELECT 
                sa.*,
                u.full_name as created_by_name
            FROM security_announcements sa
            LEFT JOIN users u ON sa.created_by = u.id
            WHERE sa.id = ?
        ", [$id]);

        if (!$announcement) {
            Response::notFound('公告不存在');
        }

        // 檢查權限
        if ($currentUser['role'] !== 'admin') {
            // 檢查是否為目標受眾
            if (!$this->isTargetUser($announcement, $currentUser)) {
                Response::forbidden('無權限查看此公告');
            }

            // 記錄閱讀狀態
            $this->markAsRead($id, $currentUser['id']);
        }

        // 解析目標對象 JSON
        if ($announcement['target_departments']) {
            $announcement['target_departments'] = json_decode($announcement['target_departments'], true);
        }
        if ($announcement['target_users']) {
            $announcement['target_users'] = json_decode($announcement['target_users'], true);
        }

        // 獲取閱讀統計（僅管理員）
        if ($currentUser['role'] === 'admin') {
            $readStats = $this->getReadStatistics($id);
            $announcement['read_statistics'] = $readStats;
        }

        Response::success($announcement);
    }

    /**
     * 創建公告
     */
    public function store(): void
    {
        // 檢查管理員權限
        if ($GLOBALS['current_user']['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }

        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'title' => 'required|max:200',
            'content' => 'required',
            'priority' => 'in:low,medium,high,urgent',
            'target_type' => 'required|in:all,department,users',
            'send_type' => 'in:immediate,scheduled'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only([
            'title', 'content', 'priority', 'category', 'target_type', 'send_type',
            'scheduled_at', 'require_acknowledgment', 'acknowledgment_deadline'
        ]);

        // 處理目標對象
        if ($data['target_type'] === 'department') {
            $targetDepartments = $request->input('target_departments', []);
            $data['target_departments'] = json_encode($targetDepartments);
        } elseif ($data['target_type'] === 'users') {
            $targetUsers = $request->input('target_users', []);
            $data['target_users'] = json_encode($targetUsers);
        }

        // 處理附件
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->handleAttachment($request->file('attachment'));
            $data['attachment_path'] = $attachmentPath;
        }

        $data['created_by'] = $GLOBALS['current_user']['id'];
        $data['status'] = 'draft';

        try {
            $this->db->beginTransaction();

            $announcementId = $this->db->insert('security_announcements', $data);

            // 如果是立即發送，直接發送
            if ($data['send_type'] === 'immediate') {
                $this->sendAnnouncement($announcementId);
            }

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('create', 'announcement', $announcementId, null, $data);

            Response::created(['id' => $announcementId], '公告創建成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('公告創建失敗：' . $e->getMessage());
        }
    }

    /**
     * 更新公告
     */
    public function update(array $params): void
    {
        $id = (int)$params['id'];

        // 檢查管理員權限
        if ($GLOBALS['current_user']['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }

        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            Response::notFound('公告不存在');
        }

        // 已發送的公告不能修改
        if ($announcement['status'] === 'sent') {
            Response::error('已發送的公告無法修改');
        }

        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'title' => 'required|max:200',
            'content' => 'required',
            'priority' => 'in:low,medium,high,urgent',
            'target_type' => 'required|in:all,department,users',
            'send_type' => 'in:immediate,scheduled'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only([
            'title', 'content', 'priority', 'category', 'target_type', 'send_type',
            'scheduled_at', 'require_acknowledgment', 'acknowledgment_deadline'
        ]);

        // 處理目標對象
        if ($data['target_type'] === 'department') {
            $targetDepartments = $request->input('target_departments', []);
            $data['target_departments'] = json_encode($targetDepartments);
        } elseif ($data['target_type'] === 'users') {
            $targetUsers = $request->input('target_users', []);
            $data['target_users'] = json_encode($targetUsers);
        }

        // 處理附件
        if ($request->hasFile('attachment')) {
            // 刪除舊附件
            if ($announcement['attachment_path'] && file_exists($announcement['attachment_path'])) {
                unlink($announcement['attachment_path']);
            }
            
            $attachmentPath = $this->handleAttachment($request->file('attachment'));
            $data['attachment_path'] = $attachmentPath;
        }

        try {
            $this->db->update('security_announcements', $data, 'id = ?', [$id]);

            // 記錄操作日誌
            $this->logAction('update', 'announcement', $id, $announcement, $data);

            Response::updated(null, '公告更新成功');

        } catch (\Exception $e) {
            Response::serverError('公告更新失敗：' . $e->getMessage());
        }
    }

    /**
     * 發送公告
     */
    public function send(array $params): void
    {
        $id = (int)$params['id'];

        // 檢查管理員權限
        if ($GLOBALS['current_user']['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }

        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            Response::notFound('公告不存在');
        }

        if ($announcement['status'] === 'sent') {
            Response::error('公告已經發送過了');
        }

        try {
            $this->sendAnnouncement($id);
            Response::success(null, '公告發送成功');

        } catch (\Exception $e) {
            Response::serverError('公告發送失敗：' . $e->getMessage());
        }
    }

    /**
     * 確認已讀
     */
    public function acknowledge(array $params): void
    {
        $id = (int)$params['id'];
        $currentUser = $GLOBALS['current_user'];

        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            Response::notFound('公告不存在');
        }

        // 檢查是否為目標受眾
        if (!$this->isTargetUser($announcement, $currentUser)) {
            Response::forbidden('無權限確認此公告');
        }

        // 檢查是否需要確認
        if (!$announcement['require_acknowledgment']) {
            Response::error('此公告不需要確認');
        }

        try {
            // 更新確認狀態
            $this->db->query("
                UPDATE announcement_reads 
                SET acknowledged_at = NOW() 
                WHERE announcement_id = ? AND user_id = ?
            ", [$id, $currentUser['id']]);

            // 如果還沒有閱讀記錄，先創建
            if ($this->db->lastInsertId() == 0) {
                $this->markAsRead($id, $currentUser['id']);
                $this->db->query("
                    UPDATE announcement_reads 
                    SET acknowledged_at = NOW() 
                    WHERE announcement_id = ? AND user_id = ?
                ", [$id, $currentUser['id']]);
            }

            Response::success(null, '確認成功');

        } catch (\Exception $e) {
            Response::serverError('確認失敗：' . $e->getMessage());
        }
    }

    /**
     * 獲取我的公告列表
     */
    public function myAnnouncements(): void
    {
        $request = new Request();
        $page = (int)$request->input('page', 1);
        $perPage = min((int)$request->input('per_page', 20), 100);
        $unreadOnly = $request->input('unread_only', false);

        $currentUser = $GLOBALS['current_user'];

        $conditions = ["sa.status = 'sent'"];
        $params = [];

        // 目標對象過濾
        $conditions[] = "(
            sa.target_type = 'all' OR 
            (sa.target_type = 'department' AND JSON_CONTAINS(sa.target_departments, ?)) OR
            (sa.target_type = 'users' AND JSON_CONTAINS(sa.target_users, ?))
        )";
        $params = array_merge($params, [
            json_encode($currentUser['department']),
            json_encode($currentUser['id'])
        ]);

        // 只顯示未讀
        if ($unreadOnly) {
            $conditions[] = "ar.id IS NULL";
        }

        $whereClause = 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                sa.*,
                ar.read_at,
                ar.acknowledged_at,
                CASE WHEN ar.id IS NOT NULL THEN 1 ELSE 0 END as is_read,
                CASE WHEN ar.acknowledged_at IS NOT NULL THEN 1 ELSE 0 END as is_acknowledged
            FROM security_announcements sa
            LEFT JOIN announcement_reads ar ON sa.id = ar.announcement_id AND ar.user_id = ?
            {$whereClause}
            ORDER BY sa.priority DESC, sa.sent_at DESC
        ";

        $allParams = array_merge([$currentUser['id']], $params);
        $result = $this->db->paginate($sql, $allParams, $page, $perPage);

        Response::paginated($result);
    }

    /**
     * 獲取閱讀統計
     */
    public function readStatistics(array $params): void
    {
        $id = (int)$params['id'];

        // 檢查管理員權限
        if ($GLOBALS['current_user']['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }

        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$id]);
        if (!$announcement) {
            Response::notFound('公告不存在');
        }

        $stats = $this->getReadStatistics($id, true);
        Response::success($stats);
    }

    /**
     * 發送公告實現
     */
    private function sendAnnouncement(int $id): void
    {
        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$id]);
        
        // 更新發送狀態
        $this->db->update('security_announcements', [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        // 這裡可以實現實際的郵件發送邏輯
        $this->sendEmailNotifications($announcement);

        // 記錄操作日誌
        $this->logAction('send', 'announcement', $id);
    }

    /**
     * 發送郵件通知
     */
    private function sendEmailNotifications(array $announcement): void
    {
        // 獲取目標用戶列表
        $targetUsers = $this->getTargetUsers($announcement);

        // 這裡應該實現實際的郵件發送邏輯
        // 簡化版本記錄到日誌
        foreach ($targetUsers as $user) {
            error_log("Send email to {$user['email']}: {$announcement['title']}");
        }
    }

    /**
     * 獲取目標用戶列表
     */
    private function getTargetUsers(array $announcement): array
    {
        $users = [];

        if ($announcement['target_type'] === 'all') {
            $users = $this->db->fetchAll("SELECT id, email, full_name FROM users WHERE status = 'active'");
        } elseif ($announcement['target_type'] === 'department') {
            $departments = json_decode($announcement['target_departments'], true) ?? [];
            if (!empty($departments)) {
                $placeholders = str_repeat('?,', count($departments) - 1) . '?';
                $users = $this->db->fetchAll(
                    "SELECT id, email, full_name FROM users WHERE status = 'active' AND department IN ($placeholders)",
                    $departments
                );
            }
        } elseif ($announcement['target_type'] === 'users') {
            $userIds = json_decode($announcement['target_users'], true) ?? [];
            if (!empty($userIds)) {
                $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
                $users = $this->db->fetchAll(
                    "SELECT id, email, full_name FROM users WHERE status = 'active' AND id IN ($placeholders)",
                    $userIds
                );
            }
        }

        return $users;
    }

    /**
     * 檢查是否為目標用戶
     */
    private function isTargetUser(array $announcement, array $user): bool
    {
        if ($announcement['target_type'] === 'all') {
            return true;
        } elseif ($announcement['target_type'] === 'department') {
            $departments = json_decode($announcement['target_departments'], true) ?? [];
            return in_array($user['department'], $departments);
        } elseif ($announcement['target_type'] === 'users') {
            $userIds = json_decode($announcement['target_users'], true) ?? [];
            return in_array($user['id'], $userIds);
        }

        return false;
    }

    /**
     * 標記為已讀
     */
    private function markAsRead(int $announcementId, int $userId): void
    {
        $request = new Request();
        
        // 檢查是否已經存在記錄
        $existing = $this->db->fetch(
            "SELECT id FROM announcement_reads WHERE announcement_id = ? AND user_id = ?",
            [$announcementId, $userId]
        );

        if (!$existing) {
            $this->db->insert('announcement_reads', [
                'announcement_id' => $announcementId,
                'user_id' => $userId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
    }

    /**
     * 獲取閱讀統計
     */
    private function getReadStatistics(int $announcementId, bool $detailed = false): array
    {
        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$announcementId]);
        $targetUsers = $this->getTargetUsers($announcement);
        $totalTargets = count($targetUsers);

        $readCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM announcement_reads WHERE announcement_id = ?",
            [$announcementId]
        )['count'];

        $ackCount = 0;
        if ($announcement['require_acknowledgment']) {
            $ackCount = $this->db->fetch(
                "SELECT COUNT(*) as count FROM announcement_reads WHERE announcement_id = ? AND acknowledged_at IS NOT NULL",
                [$announcementId]
            )['count'];
        }

        $stats = [
            'total_targets' => $totalTargets,
            'read_count' => $readCount,
            'read_rate' => $totalTargets > 0 ? round(($readCount / $totalTargets) * 100, 2) : 0,
            'unread_count' => $totalTargets - $readCount
        ];

        if ($announcement['require_acknowledgment']) {
            $stats['acknowledgment_count'] = $ackCount;
            $stats['acknowledgment_rate'] = $totalTargets > 0 ? round(($ackCount / $totalTargets) * 100, 2) : 0;
            $stats['unacknowledged_count'] = $totalTargets - $ackCount;
        }

        if ($detailed) {
            // 獲取詳細的用戶閱讀狀態
            $userStats = $this->db->fetchAll("
                SELECT 
                    u.id, u.full_name, u.department, u.email,
                    ar.read_at, ar.acknowledged_at
                FROM users u
                LEFT JOIN announcement_reads ar ON u.id = ar.user_id AND ar.announcement_id = ?
                WHERE u.status = 'active'
                ORDER BY ar.read_at DESC
            ", [$announcementId]);

            $stats['user_details'] = $userStats;
        }

        return $stats;
    }

    /**
     * 處理附件上傳
     */
    private function handleAttachment(array $file): string
    {
        $uploadDir = 'uploads/announcements/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . '_' . $file['name'];
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('附件上傳失敗');
        }

        return $filepath;
    }

    /**
     * 記錄操作日誌
     */
    private function logAction(string $action, string $module, int $targetId, ?array $oldData = null, ?array $newData = null): void
    {
        $request = new Request();
        
        $this->db->insert('system_logs', [
            'user_id' => $GLOBALS['current_user']['id'],
            'action' => $action,
            'module' => $module,
            'target_id' => $targetId,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}