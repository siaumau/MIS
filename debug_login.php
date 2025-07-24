<?php
// 載入環境變數
if (file_exists(__DIR__ . '/backend/.env')) {
    $lines = file(__DIR__ . '/backend/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// 直接連接資料庫測試
try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=mis_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "用戶找到:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "用戶名: " . $user['username'] . "\n";
        echo "密碼雜湊: " . $user['password_hash'] . "\n";
        echo "雜湊長度: " . strlen($user['password_hash']) . "\n";
        
        // 測試密碼驗證
        $result = password_verify('password', $user['password_hash']);
        echo "密碼驗證結果: " . ($result ? "成功" : "失敗") . "\n";
        
        // 生成新的雜湊來比較
        $newHash = password_hash('password', PASSWORD_DEFAULT);
        echo "新生成的雜湊: " . $newHash . "\n";
        echo "新雜湊驗證: " . (password_verify('password', $newHash) ? "成功" : "失敗") . "\n";
    } else {
        echo "未找到用戶\n";
    }
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}