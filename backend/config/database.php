<?php
/**
 * 資料庫配置檔案
 */

return [
    'default' => 'mysql',
    
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../' . ($_ENV['DB_DATABASE'] ?? 'database.sqlite'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'mis_system',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ],
        ],
    ],
    
    'redis' => [
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => $_ENV['REDIS_PORT'] ?? 6379,
        'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        'database' => $_ENV['REDIS_DB'] ?? 0,
    ],
];