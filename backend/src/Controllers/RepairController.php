<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

/**
 * 報修與維修管理控制器
 */
class RepairController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取報修單列表
     */
    public function index(): void
    {
        $request = new Request();
        $page = (int)$request->input('page', 1);
        $perPage = min((int)$request->input('per_page', 20), 100);
        $search = $request->input('search', '');
        $status = $request->input('status');
        $priority = $request->input('priority');
        $assignedTo = $request->input('assigned_to');
        $requesterId = $request->input('requester_id');

        // 構建查詢條件
        $conditions = [];
        $params = [];

        if ($search) {
            $conditions[] = "(rr.request_number LIKE ? OR rr.title LIKE ? OR rr.description LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }

        if ($status) {
            $conditions[] = "rr.status = ?";
            $params[] = $status;
        }

        if ($priority) {
            $conditions[] = "rr.priority = ?";
            $params[] = $priority;
        }

        if ($assignedTo) {
            $conditions[] = "rr.assigned_to = ?";
            $params[] = $assignedTo;
        }

        if ($requesterId) {
            $conditions[] = "rr.requester_id = ?";
            $params[] = $requesterId;
        }

        // 如果不是管理員，只能看到自己的報修單和指派給自己的單子
        $currentUser = $GLOBALS['current_user'];
        if ($currentUser['role'] !== 'admin') {
            $conditions[] = "(rr.requester_id = ? OR rr.assigned_to = ?)";
            $params = array_merge($params, [$currentUser['id'], $currentUser['id']]);
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                rr.*,
                e.name as equipment_name,
                e.property_number as equipment_property_number,
                u1.full_name as requester_name,
                u2.full_name as assigned_to_name,
                (SELECT COUNT(*) FROM repair_images ri WHERE ri.repair_id = rr.id) as image_count
            FROM repair_requests rr
            LEFT JOIN equipment e ON rr.equipment_id = e.id
            LEFT JOIN users u1 ON rr.requester_id = u1.id
            LEFT JOIN users u2 ON rr.assigned_to = u2.id
            {$whereClause}
            ORDER BY 
                CASE rr.priority 
                    WHEN 'urgent' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'medium' THEN 3 
                    WHEN 'low' THEN 4 
                END,
                rr.created_at DESC
        ";

        $result = $this->db->paginate($sql, $params, $page, $perPage);
        Response::paginated($result);
    }

    /**
     * 獲取單一報修單詳細資訊
     */
    public function show(array $params): void
    {
        $id = (int)$params['id'];

        $repair = $this->db->fetch("
            SELECT 
                rr.*,
                e.name as equipment_name,
                e.brand as equipment_brand,
                e.model as equipment_model,
                e.property_number as equipment_property_number,
                e.location as equipment_location,
                u1.full_name as requester_name,
                u1.email as requester_email,
                u1.phone as requester_phone,
                u2.full_name as assigned_to_name,
                u2.email as assigned_to_email
            FROM repair_requests rr
            LEFT JOIN equipment e ON rr.equipment_id = e.id
            LEFT JOIN users u1 ON rr.requester_id = u1.id
            LEFT JOIN users u2 ON rr.assigned_to = u2.id
            WHERE rr.id = ?
        ", [$id]);

        if (!repair) {
            Response::notFound('報修單不存在');
        }

        // 檢查權限
        $currentUser = $GLOBALS['current_user'];
        if ($currentUser['role'] !== 'admin' && 
            $repair['requester_id'] != $currentUser['id'] && 
            $repair['assigned_to'] != $currentUser['id']) {
            Response::forbidden('無權限查看此報修單');
        }

        // 獲取報修圖片
        $images = $this->db->fetchAll("
            SELECT id, filename, original_name, file_path, thumbnail_path, image_type
            FROM repair_images 
            WHERE repair_id = ? 
            ORDER BY image_type, created_at ASC
        ", [$id]);

        $repair['images'] = $images;

        // 獲取處理記錄
        $logs = $this->db->fetchAll("
            SELECT 
                rl.*,
                u.full_name as created_by_name
            FROM repair_logs rl
            LEFT JOIN users u ON rl.created_by = u.id
            WHERE rl.repair_id = ?
            ORDER BY rl.created_at ASC
        ", [$id]);

        $repair['logs'] = $logs;

        // 解析設備資訊快照
        if ($repair['equipment_info']) {
            $repair['equipment_info'] = json_decode($repair['equipment_info'], true);
        }

        Response::success($repair);
    }

    /**
     * 創建報修單
     */
    public function store(): void
    {
        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'title' => 'required|max:200',
            'description' => 'required',
            'priority' => 'in:low,medium,high,urgent',
            'equipment_id' => 'integer'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only([
            'equipment_id', 'title', 'description', 'priority', 'requester_contact'
        ]);

        $data['requester_id'] = $GLOBALS['current_user']['id'];
        $data['status'] = 'pending';
        $data['request_number'] = $this->generateRequestNumber();

        // 如果指定了設備，獲取設備資訊快照
        if ($data['equipment_id']) {
            $equipment = $this->db->fetch(
                "SELECT name, brand, model, property_number, location, ip_address FROM equipment WHERE id = ?",
                [$data['equipment_id']]
            );

            if (!$equipment) {
                Response::error('指定的設備不存在');
            }

            $data['equipment_info'] = json_encode($equipment);
        }

        try {
            $this->db->beginTransaction();

            $repairId = $this->db->insert('repair_requests', $data);

            // 記錄創建日誌
            $this->addRepairLog($repairId, 'created', null, null, '報修單已創建');

            // 處理圖片上傳
            $imageIds = $request->input('image_ids', []);
            if (!empty($imageIds)) {
                $this->attachImages($repairId, $imageIds, 'problem');
            }

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('create', 'repair', $repairId, null, $data);

            // 發送通知給管理員
            $this->notifyAdmins($repairId, 'new_repair');

            // 獲取完整的報修單資訊
            $repair = $this->db->fetch("
                SELECT rr.*, e.name as equipment_name, u.full_name as requester_name
                FROM repair_requests rr
                LEFT JOIN equipment e ON rr.equipment_id = e.id
                LEFT JOIN users u ON rr.requester_id = u.id
                WHERE rr.id = ?
            ", [$repairId]);

            Response::created($repair, '報修單創建成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('報修單創建失敗：' . $e->getMessage());
        }
    }

    /**
     * 指派報修單
     */
    public function assign(array $params): void
    {
        $id = (int)$params['id'];
        $request = new Request();

        // 檢查權限
        if ($GLOBALS['current_user']['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }

        // 驗證輸入
        $errors = $request->validate([
            'assigned_to' => 'required|integer',
            'estimated_completion' => 'max:19'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $repair = $this->db->fetch("SELECT * FROM repair_requests WHERE id = ?", [$id]);
        if (!repair) {
            Response::notFound('報修單不存在');
        }

        $assignedTo = (int)$request->input('assigned_to');
        $estimatedCompletion = $request->input('estimated_completion');

        // 檢查指派的用戶是否存在
        $assignedUser = $this->db->fetch("SELECT full_name FROM users WHERE id = ? AND status = 'active'", [$assignedTo]);
        if (!$assignedUser) {
            Response::error('指派的用戶不存在或已停用');
        }

        try {
            $this->db->beginTransaction();

            $updateData = [
                'assigned_to' => $assignedTo,
                'status' => 'assigned'
            ];

            if ($estimatedCompletion) {
                $updateData['estimated_completion'] = $estimatedCompletion;
            }

            $this->db->update('repair_requests', $updateData, 'id = ?', [$id]);

            // 記錄指派日誌
            $this->addRepairLog($id, 'assigned', $repair['assigned_to'], $assignedTo, 
                "報修單已指派給 {$assignedUser['full_name']}");

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('assign', 'repair', $id, $repair, $updateData);

            // 發送通知給被指派的用戶
            $this->notifyUser($assignedTo, $id, 'repair_assigned');

            Response::success(null, '報修單指派成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('報修單指派失敗：' . $e->getMessage());
        }
    }

    /**
     * 更新報修單狀態
     */
    public function updateStatus(array $params): void
    {
        $id = (int)$params['id'];
        $request = new Request();

        // 驗證輸入
        $errors = $request->validate([
            'status' => 'required|in:pending,assigned,in_progress,completed,cancelled',
            'comment' => 'max:1000'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $repair = $this->db->fetch("SELECT * FROM repair_requests WHERE id = ?", [$id]);
        if (!repair) {
            Response::notFound('報修單不存在');
        }

        // 檢查權限
        $currentUser = $GLOBALS['current_user'];
        if ($currentUser['role'] !== 'admin' && $repair['assigned_to'] != $currentUser['id']) {
            Response::forbidden('無權限更新此報修單');
        }

        $newStatus = $request->input('status');
        $comment = $request->input('comment');
        $resolution = $request->input('resolution');
        $cost = $request->input('cost');
        $supplier = $request->input('supplier');

        try {
            $this->db->beginTransaction();

            $updateData = ['status' => $newStatus];

            // 如果狀態為完成，記錄完成時間
            if ($newStatus === 'completed') {
                $updateData['actual_completion'] = date('Y-m-d H:i:s');
                
                if ($resolution) {
                    $updateData['resolution'] = $resolution;
                }
                if ($cost !== null) {
                    $updateData['cost'] = (float)$cost;
                }
                if ($supplier) {
                    $updateData['supplier'] = $supplier;
                }
            }

            $this->db->update('repair_requests', $updateData, 'id = ?', [$id]);

            // 記錄狀態變更日誌
            $this->addRepairLog($id, 'status_changed', $repair['status'], $newStatus, $comment);

            // 處理解決方案圖片
            if ($newStatus === 'completed') {
                $solutionImageIds = $request->input('solution_image_ids', []);
                if (!empty($solutionImageIds)) {
                    $this->attachImages($id, $solutionImageIds, 'solution');
                }
            }

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('update_status', 'repair', $id, $repair, $updateData);

            // 發送通知
            $this->notifyStatusChange($id, $newStatus);

            Response::success(null, '報修單狀態更新成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('狀態更新失敗：' . $e->getMessage());
        }
    }

    /**
     * 添加評論
     */
    public function addComment(array $params): void
    {
        $id = (int)$params['id'];
        $request = new Request();

        // 驗證輸入
        $errors = $request->validate([
            'comment' => 'required|max:1000'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $repair = $this->db->fetch("SELECT * FROM repair_requests WHERE id = ?", [$id]);
        if (!repair) {
            Response::notFound('報修單不存在');
        }

        // 檢查權限
        $currentUser = $GLOBALS['current_user'];
        if ($currentUser['role'] !== 'admin' && 
            $repair['requester_id'] != $currentUser['id'] && 
            $repair['assigned_to'] != $currentUser['id']) {
            Response::forbidden('無權限對此報修單添加評論');
        }

        $comment = $request->input('comment');

        try {
            $this->addRepairLog($id, 'comment_added', null, null, $comment);

            // 記錄操作日誌
            $this->logAction('add_comment', 'repair', $id, null, ['comment' => $comment]);

            Response::success(null, '評論添加成功');

        } catch (\Exception $e) {
            Response::serverError('評論添加失敗：' . $e->getMessage());
        }
    }

    /**
     * 獲取報修統計
     */
    public function statistics(): void
    {
        $currentUser = $GLOBALS['current_user'];
        
        // 基本統計
        $stats = [];

        if ($currentUser['role'] === 'admin') {
            // 管理員可以看到全部統計
            $stats['total'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests")['count'];
            $stats['pending'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests WHERE status = 'pending'")['count'];
            $stats['in_progress'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests WHERE status IN ('assigned', 'in_progress')")['count'];
            $stats['completed'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests WHERE status = 'completed'")['count'];
            $stats['urgent'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests WHERE priority = 'urgent' AND status != 'completed'")['count'];
        } else {
            // 一般用戶只能看到自己相關的統計
            $whereClause = "WHERE (requester_id = ? OR assigned_to = ?)";
            $params = [$currentUser['id'], $currentUser['id']];
            
            $stats['total'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests $whereClause", $params)['count'];
            $stats['pending'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests $whereClause AND status = 'pending'", array_merge($params, ['pending']))['count'];
            $stats['in_progress'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests $whereClause AND status IN ('assigned', 'in_progress')", $params)['count'];
            $stats['completed'] = $this->db->fetch("SELECT COUNT(*) as count FROM repair_requests $whereClause AND status = 'completed'", array_merge($params, ['completed']))['count'];
        }

        // 本月統計
        $thisMonth = date('Y-m');
        $monthlyStats = $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                AVG(CASE WHEN status = 'completed' AND actual_completion IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, created_at, actual_completion) END) as avg_resolution_hours
            FROM repair_requests 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ", [$thisMonth]);

        $stats['this_month'] = $monthlyStats;

        // 優先級分布
        $priorityStats = $this->db->fetchAll("
            SELECT priority, COUNT(*) as count 
            FROM repair_requests 
            WHERE status != 'completed'
            GROUP BY priority
        ");

        $stats['priority_distribution'] = $priorityStats;

        Response::success($stats);
    }

    /**
     * 生成報修單編號
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'R';
        $date = date('Ymd');
        
        // 獲取今日最大序號
        $result = $this->db->fetch("
            SELECT MAX(CAST(SUBSTRING(request_number, 10) AS UNSIGNED)) as max_seq
            FROM repair_requests 
            WHERE request_number LIKE ?
        ", [$prefix . $date . '%']);

        $nextSeq = ($result['max_seq'] ?? 0) + 1;
        
        return $prefix . $date . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * 關聯圖片到報修單
     */
    private function attachImages(int $repairId, array $imageIds, string $imageType): void
    {
        foreach ($imageIds as $imageId) {
            $this->db->update('repair_images', [
                'repair_id' => $repairId,
                'image_type' => $imageType
            ], 'id = ?', [$imageId]);
        }
    }

    /**
     * 添加維修記錄
     */
    private function addRepairLog(int $repairId, string $actionType, $oldValue, $newValue, string $comment = null): void
    {
        $this->db->insert('repair_logs', [
            'repair_id' => $repairId,
            'action_type' => $actionType,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'comment' => $comment,
            'created_by' => $GLOBALS['current_user']['id']
        ]);
    }

    /**
     * 通知管理員
     */
    private function notifyAdmins(int $repairId, string $type): void
    {
        // 這裡可以實現郵件或推播通知
        // 簡化版本記錄到日誌
        error_log("Notify admins: repair $repairId, type: $type");
    }

    /**
     * 通知用戶
     */
    private function notifyUser(int $userId, int $repairId, string $type): void
    {
        // 這裡可以實現郵件或推播通知
        error_log("Notify user $userId: repair $repairId, type: $type");
    }

    /**
     * 通知狀態變更
     */
    private function notifyStatusChange(int $repairId, string $newStatus): void
    {
        // 這裡可以實現郵件或推播通知
        error_log("Notify status change: repair $repairId, status: $newStatus");
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