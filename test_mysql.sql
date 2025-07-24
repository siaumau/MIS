-- 測試MySQL連接和創建表
CREATE DATABASE IF NOT EXISTS mis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mis_system;

-- 用戶表
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL COMMENT '用戶名',
    email VARCHAR(100) UNIQUE NOT NULL COMMENT '電子郵件',
    password_hash VARCHAR(255) NOT NULL COMMENT '密碼雜湊',
    full_name VARCHAR(100) NOT NULL COMMENT '姓名',
    department VARCHAR(50) COMMENT '部門',
    position VARCHAR(50) COMMENT '職位',
    phone VARCHAR(20) COMMENT '電話',
    role ENUM('admin', 'user', 'viewer') DEFAULT 'user' COMMENT '角色',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '狀態',
    avatar VARCHAR(255) COMMENT '頭像路徑',
    last_login TIMESTAMP NULL COMMENT '最後登入時間',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT '用戶表';

-- 插入預設管理員用戶 (密碼: password)
INSERT INTO users (username, email, password_hash, full_name, department, position, role) VALUES 
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系統管理員', 'IT部門', '系統管理員', 'admin');