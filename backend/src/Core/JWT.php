<?php

namespace App\Core;

/**
 * JWT Token 管理類
 */
class JWT
{
    private static string $secret;
    private static string $algorithm = 'HS256';
    private static int $expire;

    public static function init(): void
    {
        $config = include __DIR__ . '/../../config/app.php';
        self::$secret = $config['jwt']['secret'];
        self::$algorithm = $config['jwt']['algorithm'];
        self::$expire = $config['jwt']['expire'];
    }

    /**
     * 生成 JWT Token
     */
    public static function encode(array $payload, int $expire = null): string
    {
        if (!isset(self::$secret)) {
            self::init();
        }

        $header = [
            'typ' => 'JWT',
            'alg' => self::$algorithm
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + ($expire ?? self::$expire);

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * 解碼 JWT Token
     */
    public static function decode(string $token): ?array
    {
        if (!self::$secret) {
            self::init();
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // 驗證簽名
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, self::$secret, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        // 解碼載荷
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
        if (!$payload) {
            return null;
        }

        // 檢查過期時間
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * 驗證 Token
     */
    public static function verify(string $token): bool
    {
        return self::decode($token) !== null;
    }

    /**
     * 從 Token 獲取用戶 ID
     */
    public static function getUserId(string $token): ?int
    {
        $payload = self::decode($token);
        return $payload['user_id'] ?? null;
    }

    /**
     * 檢查 Token 是否即將過期
     */
    public static function isExpiringSoon(string $token, int $threshold = 300): bool
    {
        $payload = self::decode($token);
        if (!$payload || !isset($payload['exp'])) {
            return true;
        }

        return ($payload['exp'] - time()) <= $threshold;
    }

    /**
     * 刷新 Token
     */
    public static function refresh(string $token): ?string
    {
        $payload = self::decode($token);
        if (!$payload) {
            return null;
        }

        // 移除舊的時間戳
        unset($payload['iat'], $payload['exp']);

        return self::encode($payload);
    }

    /**
     * Base64 URL 編碼
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL 解碼
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * 生成隨機密鑰
     */
    public static function generateSecret(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * 將 Token 加入黑名單（存儲到資料庫或快取）
     */
    public static function blacklist(string $token): bool
    {
        $payload = self::decode($token);
        if (!$payload) {
            return false;
        }

        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);

        try {
            $db->insert('user_tokens', [
                'user_id' => $payload['user_id'],
                'token_hash' => $tokenHash,
                'expires_at' => date('Y-m-d H:i:s', $payload['exp'])
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to blacklist token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 檢查 Token 是否在黑名單中
     */
    public static function isBlacklisted(string $token): bool
    {
        $db = Database::getInstance();
        $tokenHash = hash('sha256', $token);

        $result = $db->fetch(
            "SELECT id FROM user_tokens WHERE token_hash = ? AND expires_at > NOW()",
            [$tokenHash]
        );

        return $result !== null;
    }

    /**
     * 清理過期的黑名單 Token
     */
    public static function cleanExpiredTokens(): int
    {
        $db = Database::getInstance();
        return $db->delete('user_tokens', 'expires_at <= NOW()');
    }
}