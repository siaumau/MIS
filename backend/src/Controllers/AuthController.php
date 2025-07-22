<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Core\JWT;

/**
 * 用戶認證控制器
 */
class AuthController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 用戶登入
     */
    public function login(): void
    {
        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        // 查找用戶
        $user = $this->db->fetch(
            "SELECT id, username, email, password_hash, full_name, department, position, role, status 
             FROM users WHERE (username = ? OR email = ?) AND status = 'active'",
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::error('用戶名或密碼錯誤', 401);
        }

        // 更新最後登入時間
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

        // 生成 JWT Token
        $token = JWT::encode([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ]);

        // 記錄登入日誌
        $this->logUserAction($user['id'], 'login', 'auth', null, [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // 移除密碼雜湊
        unset($user['password_hash']);

        Response::success([
            'user' => $user,
            'token' => $token,
            'expires_in' => 3600
        ], '登入成功');
    }

    /**
     * 用戶登出
     */
    public function logout(): void
    {
        $request = new Request();
        $token = $request->bearerToken();

        if ($token) {
            // 將 Token 加入黑名單
            JWT::blacklist($token);
        }

        // 記錄登出日誌
        if (isset($GLOBALS['current_user'])) {
            $this->logUserAction($GLOBALS['current_user']['id'], 'logout', 'auth');
        }

        Response::success(null, '登出成功');
    }

    /**
     * 刷新 Token
     */
    public function refresh(): void
    {
        $request = new Request();
        $token = $request->bearerToken();

        if (!$token) {
            Response::unauthorized('未提供 Token');
        }

        // 檢查 Token 是否即將過期
        if (!JWT::isExpiringSoon($token, 600)) { // 10分鐘內才允許刷新
            Response::error('Token 尚未到期');
        }

        $newToken = JWT::refresh($token);
        if (!$newToken) {
            Response::unauthorized('無法刷新 Token');
        }

        // 將舊 Token 加入黑名單
        JWT::blacklist($token);

        Response::success([
            'token' => $newToken,
            'expires_in' => 3600
        ], 'Token 刷新成功');
    }

    /**
     * 獲取當前用戶資訊
     */
    public function me(): void
    {
        if (!isset($GLOBALS['current_user'])) {
            Response::unauthorized('請先登入');
        }

        $user = $GLOBALS['current_user'];
        
        // 獲取用戶額外資訊
        $userData = $this->db->fetch(
            "SELECT id, username, email, full_name, department, position, role, status, avatar, created_at, last_login
             FROM users WHERE id = ?",
            [$user['id']]
        );

        Response::success($userData);
    }

    /**
     * 修改密碼
     */
    public function changePassword(): void
    {
        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $currentPassword = $request->input('current_password');
        $newPassword = $request->input('new_password');
        $confirmPassword = $request->input('confirm_password');

        // 檢查新密碼確認
        if ($newPassword !== $confirmPassword) {
            Response::error('新密碼確認不符');
        }

        $user = $GLOBALS['current_user'];
        
        // 驗證當前密碼
        $currentUser = $this->db->fetch(
            "SELECT password_hash FROM users WHERE id = ?",
            [$user['id']]
        );

        if (!password_verify($currentPassword, $currentUser['password_hash'])) {
            Response::error('當前密碼錯誤');
        }

        // 更新密碼
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update('users', [
            'password_hash' => $newPasswordHash
        ], 'id = ?', [$user['id']]);

        // 記錄操作日誌
        $this->logUserAction($user['id'], 'change_password', 'auth');

        Response::success(null, '密碼修改成功');
    }

    /**
     * 更新個人資料
     */
    public function updateProfile(): void
    {
        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'full_name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'max:20'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $user = $GLOBALS['current_user'];
        $updateData = $request->only(['full_name', 'email', 'phone']);

        // 檢查郵箱是否已被其他用戶使用
        if ($updateData['email'] !== $user['email']) {
            $existingUser = $this->db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$updateData['email'], $user['id']]
            );

            if ($existingUser) {
                Response::error('該郵箱已被其他用戶使用');
            }
        }

        // 更新用戶資料
        $this->db->update('users', $updateData, 'id = ?', [$user['id']]);

        // 記錄操作日誌
        $this->logUserAction($user['id'], 'update_profile', 'auth', null, $updateData);

        // 獲取更新後的用戶資料
        $updatedUser = $this->db->fetch(
            "SELECT id, username, email, full_name, department, position, role, phone, avatar
             FROM users WHERE id = ?",
            [$user['id']]
        );

        Response::success($updatedUser, '個人資料更新成功');
    }

    /**
     * 記錄用戶操作日誌
     */
    private function logUserAction(int $userId, string $action, string $module, ?int $targetId = null, ?array $data = null): void
    {
        $request = new Request();
        
        $this->db->insert('system_logs', [
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'target_id' => $targetId,
            'new_data' => $data ? json_encode($data) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}