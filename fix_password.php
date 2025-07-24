<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3306;dbname=mis_system;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 生成正確的密碼雜湊
    $correctHash = password_hash('password', PASSWORD_DEFAULT);
    echo "生成的密碼雜湊: " . $correctHash . "\n";
    echo "雜湊長度: " . strlen($correctHash) . "\n";
    
    // 使用prepared statement更新
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $result = $stmt->execute([$correctHash]);
    
    if ($result) {
        echo "密碼更新成功\n";
        
        // 驗證更新結果
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        echo "資料庫中的雜湊: " . $user['password_hash'] . "\n";
        echo "資料庫雜湊長度: " . strlen($user['password_hash']) . "\n";
        echo "驗證結果: " . (password_verify('password', $user['password_hash']) ? "成功" : "失敗") . "\n";
    } else {
        echo "密碼更新失敗\n";
    }
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
}