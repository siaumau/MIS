<?php
/**
 * 應用程式配置檔案
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'IT資產與報修管理系統',
    'version' => '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Taipei',
    
    // JWT 設定
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'your-secret-key',
        'algorithm' => 'HS256',
        'expire' => (int)($_ENV['JWT_EXPIRE'] ?? 3600), // 1 hour
        'refresh_expire' => (int)($_ENV['JWT_REFRESH_EXPIRE'] ?? 604800), // 7 days
    ],
    
    // 檔案上傳設定
    'upload' => [
        'max_file_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880), // 5MB
        'allowed_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        ],
        'paths' => [
            'equipment' => 'uploads/equipment/',
            'repair' => 'uploads/repair/',
            'topology' => 'uploads/topology/',
            'announcement' => 'uploads/announcements/',
            'avatars' => 'uploads/avatars/',
        ],
    ],
    
    // 郵件設定
    'mail' => [
        'driver' => $_ENV['MAIL_DRIVER'] ?? 'smtp',
        'host' => $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com',
        'port' => (int)($_ENV['MAIL_PORT'] ?? 587),
        'username' => $_ENV['MAIL_USERNAME'] ?? '',
        'password' => $_ENV['MAIL_PASSWORD'] ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@company.com',
            'name' => $_ENV['MAIL_FROM_NAME'] ?? 'MIS系統',
        ],
    ],
    
    // 快取設定
    'cache' => [
        'default' => $_ENV['CACHE_DRIVER'] ?? 'file',
        'ttl' => (int)($_ENV['CACHE_TTL'] ?? 3600),
        'prefix' => $_ENV['CACHE_PREFIX'] ?? 'mis_',
    ],
    
    // 分頁設定
    'pagination' => [
        'per_page' => (int)($_ENV['PAGINATION_PER_PAGE'] ?? 20),
        'max_per_page' => (int)($_ENV['PAGINATION_MAX_PER_PAGE'] ?? 100),
    ],
    
    // 安全設定
    'security' => [
        'password_min_length' => 8,
        'password_require_special' => true,
        'max_login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'csrf_token_expire' => 3600,
    ],
    
    // 系統日誌設定
    'logging' => [
        'level' => $_ENV['LOG_LEVEL'] ?? 'info',
        'max_files' => (int)($_ENV['LOG_MAX_FILES'] ?? 30),
        'path' => $_ENV['LOG_PATH'] ?? 'logs/',
    ],
];