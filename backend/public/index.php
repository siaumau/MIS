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

// 設定 CORS 標頭 - 簡化配置
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
header('Access-Control-Max-Age: 86400');

// 處理 OPTIONS 預檢請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
    header('Access-Control-Max-Age: 86400');
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