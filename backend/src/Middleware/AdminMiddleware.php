<?php

namespace App\Middleware;

use App\Core\Response;

/**
 * 管理員權限中介軟體
 */
class AdminMiddleware
{
    public function handle(): void
    {
        // 確保已通過認證中介軟體
        if (!isset($GLOBALS['current_user'])) {
            Response::unauthorized('請先登入');
        }

        $user = $GLOBALS['current_user'];

        // 檢查是否為管理員
        if ($user['role'] !== 'admin') {
            Response::forbidden('需要管理員權限');
        }
    }
}