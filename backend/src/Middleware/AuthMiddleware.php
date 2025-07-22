<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\JWT;
use App\Core\Database;

/**
 * 用戶認證中介軟體
 */
class AuthMiddleware
{
    public function handle(): void
    {
        $request = new Request();
        $token = $request->bearerToken();

        if (!$token) {
            Response::unauthorized('未提供認證 Token');
        }

        // 檢查 Token 是否在黑名單中
        if (JWT::isBlacklisted($token)) {
            Response::unauthorized('Token 已失效');
        }

        // 驗證 Token
        $payload = JWT::decode($token);
        if (!$payload) {
            Response::unauthorized('無效的 Token');
        }

        // 檢查用戶是否存在且為活躍狀態
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT id, username, email, full_name, role, status FROM users WHERE id = ? AND status = 'active'",
            [$payload['user_id']]
        );

        if (!$user) {
            Response::unauthorized('用戶不存在或已被停用');
        }

        // 將用戶資訊存儲到全域變數中
        $GLOBALS['current_user'] = $user;
        $GLOBALS['current_token'] = $token;
    }
}