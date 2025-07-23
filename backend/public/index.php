<?php

// 錯誤報告設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 設定時區
date_default_timezone_set('Asia/Taipei');

// 載入環境變數
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// 自動載入
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// 設定 CORS 標頭
// 動態允許來源
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
$allowed_origins = [
    'http://localhost:5173', // Vite 預設
    'http://localhost:5174', 
    'http://localhost:40000',
    'http://localhost:40002',
    'http://127.0.0.1:5173',
    'http://127.0.0.1:5174',
    'http://127.0.0.1:40000',
    'http://127.0.0.1:40002',
    'http://192.168.0.234:40000',
    'http://192.168.0.234:40002'
];

if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
} else {
    header('Access-Control-Allow-Origin: *'); // Fallback
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
header('Access-Control-Max-Age: 86400');
header('Vary: Origin');

// 處理 OPTIONS 預檢請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // 重複設定 CORS 標頭以確保 OPTIONS 請求有正確的回應
    if (in_array($origin, $allowed_origins)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    } else {
        header('Access-Control-Allow-Origin: *');
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Max-Age: 86400');
    header('Vary: Origin');
    http_response_code(200);
    exit();
}

// 設定 JSON 回應類型
header('Content-Type: application/json; charset=utf-8');

try {
    // 載入路由
    $router = include __DIR__ . '/../routes/api.php';
    
    // 解析路由
    $router->resolve();
    
} catch (Exception $e) {
    // 錯誤處理
    $errorResponse = [
        'success' => false,
        'message' => '伺服器錯誤',
        'error' => $e->getMessage()
    ];
    
    // 在開發環境中顯示詳細錯誤
    if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
        $errorResponse['trace'] = $e->getTraceAsString();
        $errorResponse['file'] = $e->getFile();
        $errorResponse['line'] = $e->getLine();
    }
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    // 記錄錯誤到日誌
    error_log('API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
}