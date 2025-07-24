<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use PDO;
use Exception;

class UserController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取所有用戶列表（用於下拉選單）
     */
    public function index()
    {
        try {
            $sql = "SELECT id, username, email, full_name, department, position, office_location, extension, phone, role, status
                    FROM users
                    WHERE status = 'active'
                    ORDER BY office_location, department, full_name";

            $stmt = $this->db->query($sql);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 格式化設備地點顯示
            foreach ($users as &$user) {
                $user['office_location_text'] = $user['office_location'] === 'taipei' ? '台北' : '彰化';
                $user['display_name'] = $user['full_name'] . ' (' . $user['department'] . ' - ' . $user['office_location_text'] . ')';
            }

            Response::json([
                'success' => true,
                'data' => $users
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取用戶列表失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 獲取單一用戶詳情
     */
    public function show($id)
    {
        try {
            // 處理路由參數可能是數組的情況
            $actualId = is_array($id) ? $id['id'] : $id;
            $userId = (int)$actualId;
            // error_log removed to avoid array to string conversion warning
            
            $stmt = $this->db->query(
                "SELECT id, username, email, full_name, department, position, office_location, extension, phone, role, status, avatar, last_login, created_at, updated_at
                 FROM users
                 WHERE id = ?",
                [$userId]
            );

            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            // error_log removed

            if (!$userData) {
                Response::json([
                    'success' => false,
                    'message' => '用戶不存在',
                    'errors' => ['id' => '找不到指定的用戶'],
                ], 404);
                return;
            }
            $userData['office_location_text'] = $userData['office_location'] === 'taipei' ? '台北' : '彰化';

            Response::json([
                'success' => true,
                'data' => $userData
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取用戶失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 創建新用戶
     */
    public function store()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // 必填欄位驗證
            $required = ['username', 'email', 'full_name', 'department'];
            $errors = [];

            foreach ($required as $field) {
                if (!isset($input[$field]) || empty(trim($input[$field]))) {
                    $errors[$field] = $field . '是必填項目';
                }
            }

            if (!empty($errors)) {
                Response::json([
                    'success' => false,
                    'message' => '驗證失敗',
                    'errors' => $errors
                ], 400);
                return;
            }

            // 檢查用戶名和email是否已存在
            $existingUser = $this->db->query(
                "SELECT id FROM users WHERE username = ? OR email = ?",
                [$input['username'], $input['email']]
            )->fetch();

            if ($existingUser) {
                Response::json([
                    'success' => false,
                    'message' => '用戶名或電子郵件已存在',
                    'errors' => ['duplicate' => '此用戶名或電子郵件已被使用']
                ], 400);
                return;
            }

            // 準備用戶數據
            $userData = [
                'username' => trim($input['username']),
                'email' => trim($input['email']),
                'password_hash' => password_hash($input['password'] ?? 'password123', PASSWORD_DEFAULT),
                'full_name' => trim($input['full_name']),
                'department' => trim($input['department']),
                'position' => trim($input['position'] ?? ''),
                'office_location' => $input['office_location'] ?? 'taipei',
                'extension' => trim($input['extension'] ?? ''),
                'phone' => trim($input['phone'] ?? ''),
                'role' => $input['role'] ?? 'user',
                'status' => $input['status'] ?? 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->db->insert('users', $userData);

            if ($userId) {
                $user = $this->db->query(
                    "SELECT id, username, full_name, department, office_location FROM users WHERE id = ?",
                    [$userId]
                )->fetch(PDO::FETCH_ASSOC);

                Response::json([
                    'success' => true,
                    'message' => '用戶創建成功',
                    'data' => $user
                ], 201);
            } else {
                Response::json([
                    'success' => false,
                    'message' => '用戶創建失敗',
                    'errors' => ['database' => '資料庫操作失敗']
                ], 500);
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '用戶創建失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 更新用戶資訊
     */
    public function update($id)
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // 處理路由參數可能是數組的情況
            $actualId = is_array($id) ? $id['id'] : $id;
            $userId = (int)$actualId;
            // error_log removed to avoid array to string conversion warning
            
            // 檢查用戶是否存在
            $stmt = $this->db->query("SELECT id FROM users WHERE id = ?", [$userId]);
            $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
            // error_log removed

            if (!$existingUser) {
                Response::json([
                    'success' => false,
                    'message' => '用戶不存在',
                    'errors' => ['id' => '找不到指定的用戶']
                ], 404);
                return;
            }

            // 準備更新數據
            $updateData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $allowedFields = ['email', 'full_name', 'department', 'position', 'office_location', 'extension', 'phone', 'role', 'status'];
            foreach ($allowedFields as $field) {
                if (isset($input[$field])) {
                    $updateData[$field] = trim($input[$field]);
                }
            }

            $updated = $this->db->update('users', $updateData, "id = ?", [$userId]);

            if ($updated) {
                $stmt = $this->db->query(
                    "SELECT id, username, email, full_name, department, position, office_location, extension, phone, role, status FROM users WHERE id = ?",
                    [$userId]
                );
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                Response::json([
                    'success' => true,
                    'message' => '用戶更新成功',
                    'data' => $user
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => '用戶更新失敗',
                    'errors' => ['database' => '資料庫操作失敗']
                ], 500);
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '用戶更新失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
