-- IT 資產與報修管理系統資料庫設計
-- 建立日期: 2024-12-01

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS mis_system;
CREATE DATABASE mis_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mis_system;

-- ===========================
-- 用戶與權限管理
-- ===========================

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

-- JWT Token 表 (用於管理登入狀態)
CREATE TABLE user_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL COMMENT 'Token 雜湊',
    expires_at TIMESTAMP NOT NULL COMMENT '過期時間',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires_at (expires_at)
) COMMENT 'JWT Token 表';

-- ===========================
-- 資產設備管理
-- ===========================

-- 設備分類表
CREATE TABLE equipment_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL COMMENT '分類名稱',
    description TEXT COMMENT '分類描述',
    parent_id INT NULL COMMENT '父分類 ID',
    sort_order INT DEFAULT 0 COMMENT '排序',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES equipment_categories(id) ON DELETE SET NULL
) COMMENT '設備分類表';

-- 設備表
CREATE TABLE equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '設備名稱',
    category_id INT COMMENT '分類 ID',
    brand VARCHAR(50) COMMENT '品牌',
    model VARCHAR(100) COMMENT '型號',
    serial_number VARCHAR(100) COMMENT '序號',
    property_number VARCHAR(50) COMMENT '財產編號',
    ip_address VARCHAR(45) COMMENT 'IP 位址',
    mac_address VARCHAR(17) COMMENT 'MAC 位址',
    location VARCHAR(100) COMMENT '放置位置',
    department VARCHAR(50) COMMENT '使用部門',
    responsible_person VARCHAR(50) COMMENT '負責人',
    purchase_date DATE COMMENT '採購日期',
    warranty_end_date DATE COMMENT '保固到期日',
    price DECIMAL(10, 2) COMMENT '價格',
    vendor VARCHAR(100) COMMENT '供應商',
    status ENUM('active', 'inactive', 'maintenance', 'retired') DEFAULT 'active' COMMENT '狀態',
    description TEXT COMMENT '備註說明',
    specifications JSON COMMENT '規格資訊 (JSON)',
    created_by INT COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_ip_address (ip_address),
    INDEX idx_property_number (property_number),
    INDEX idx_status (status),
    INDEX idx_department (department)
) COMMENT '設備表';

-- 設備圖片表
CREATE TABLE equipment_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    equipment_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL COMMENT '檔案名稱',
    original_name VARCHAR(255) NOT NULL COMMENT '原始檔名',
    file_path VARCHAR(500) NOT NULL COMMENT '檔案路徑',
    thumbnail_path VARCHAR(500) COMMENT '縮圖路徑',
    file_size INT NOT NULL COMMENT '檔案大小',
    mime_type VARCHAR(50) NOT NULL COMMENT 'MIME 類型',
    width INT COMMENT '圖片寬度',
    height INT COMMENT '圖片高度',
    is_primary BOOLEAN DEFAULT FALSE COMMENT '是否為主圖',
    uploaded_by INT COMMENT '上傳者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment_id (equipment_id)
) COMMENT '設備圖片表';

-- ===========================
-- 報修與維修管理
-- ===========================

-- 報修單表
CREATE TABLE repair_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_number VARCHAR(20) UNIQUE NOT NULL COMMENT '報修編號',
    equipment_id INT COMMENT '故障設備 ID',
    equipment_info JSON COMMENT '設備資訊快照',
    title VARCHAR(200) NOT NULL COMMENT '故障標題',
    description TEXT NOT NULL COMMENT '故障描述',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' COMMENT '優先等級',
    status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending' COMMENT '處理狀態',
    requester_id INT NOT NULL COMMENT '報修人 ID',
    requester_contact VARCHAR(100) COMMENT '報修人聯絡方式',
    assigned_to INT COMMENT '指派維修人員',
    estimated_completion DATETIME COMMENT '預計完成時間',
    actual_completion DATETIME COMMENT '實際完成時間',
    resolution TEXT COMMENT '解決方案',
    cost DECIMAL(10, 2) COMMENT '維修費用',
    supplier VARCHAR(100) COMMENT '維修廠商',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_request_number (request_number),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_requester_id (requester_id),
    INDEX idx_assigned_to (assigned_to)
) COMMENT '報修單表';

-- 報修圖片表
CREATE TABLE repair_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    repair_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL COMMENT '檔案名稱',
    original_name VARCHAR(255) NOT NULL COMMENT '原始檔名',
    file_path VARCHAR(500) NOT NULL COMMENT '檔案路徑',
    thumbnail_path VARCHAR(500) COMMENT '縮圖路徑',
    file_size INT NOT NULL COMMENT '檔案大小',
    image_type ENUM('problem', 'solution') DEFAULT 'problem' COMMENT '圖片類型',
    uploaded_by INT COMMENT '上傳者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repair_id) REFERENCES repair_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_repair_id (repair_id)
) COMMENT '報修圖片表';

-- 維修記錄表
CREATE TABLE repair_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    repair_id INT NOT NULL,
    action_type ENUM('created', 'assigned', 'status_changed', 'comment_added', 'completed') NOT NULL COMMENT '操作類型',
    old_value TEXT COMMENT '舊值',
    new_value TEXT COMMENT '新值',
    comment TEXT COMMENT '備註',
    created_by INT NOT NULL COMMENT '操作者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repair_id) REFERENCES repair_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_repair_id (repair_id),
    INDEX idx_created_at (created_at)
) COMMENT '維修記錄表';

-- ===========================
-- 資訊安全佈達模組
-- ===========================

-- 安全公告表
CREATE TABLE security_announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL COMMENT '公告標題',
    content TEXT NOT NULL COMMENT '公告內容',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' COMMENT '重要等級',
    category VARCHAR(50) COMMENT '公告分類',
    target_type ENUM('all', 'department', 'users') DEFAULT 'all' COMMENT '發送對象類型',
    target_departments JSON COMMENT '目標部門 (JSON 陣列)',
    target_users JSON COMMENT '目標用戶 (JSON 陣列)',
    send_type ENUM('immediate', 'scheduled') DEFAULT 'immediate' COMMENT '發送方式',
    scheduled_at DATETIME COMMENT '排程發送時間',
    sent_at DATETIME COMMENT '實際發送時間',
    status ENUM('draft', 'sent', 'cancelled') DEFAULT 'draft' COMMENT '狀態',
    require_acknowledgment BOOLEAN DEFAULT TRUE COMMENT '是否需要確認閱讀',
    acknowledgment_deadline DATETIME COMMENT '確認截止日期',
    attachment_path VARCHAR(500) COMMENT '附件路徑',
    created_by INT NOT NULL COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_scheduled_at (scheduled_at)
) COMMENT '安全公告表';

-- 公告閱讀記錄表
CREATE TABLE announcement_reads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    announcement_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '閱讀時間',
    acknowledged_at TIMESTAMP NULL COMMENT '確認時間',
    ip_address VARCHAR(45) COMMENT '閱讀 IP',
    user_agent TEXT COMMENT '瀏覽器資訊',
    FOREIGN KEY (announcement_id) REFERENCES security_announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read (announcement_id, user_id),
    INDEX idx_announcement_id (announcement_id),
    INDEX idx_user_id (user_id)
) COMMENT '公告閱讀記錄表';

-- ===========================
-- 網路拓樸圖管理
-- ===========================

-- 網路拓樸圖表
CREATE TABLE network_topologies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '拓樸圖名稱',
    location VARCHAR(100) COMMENT '位置 (如彰化、台北)',
    description TEXT COMMENT '描述',
    image_path VARCHAR(500) COMMENT '拓樸圖路徑',
    version VARCHAR(20) DEFAULT '1.0' COMMENT '版本',
    is_active BOOLEAN DEFAULT TRUE COMMENT '是否啟用',
    created_by INT COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_location (location)
) COMMENT '網路拓樸圖表';

-- 拓樸圖節點表
CREATE TABLE topology_nodes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    topology_id INT NOT NULL,
    equipment_id INT COMMENT '關聯設備 ID',
    node_type ENUM('router', 'switch', 'server', 'pc', 'printer', 'other') NOT NULL COMMENT '節點類型',
    label VARCHAR(100) NOT NULL COMMENT '節點標籤',
    position_x INT NOT NULL COMMENT 'X 座標',
    position_y INT NOT NULL COMMENT 'Y 座標',
    icon VARCHAR(100) COMMENT '圖示',
    color VARCHAR(20) DEFAULT '#000000' COMMENT '顏色',
    notes TEXT COMMENT '備註',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (topology_id) REFERENCES network_topologies(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL,
    INDEX idx_topology_id (topology_id)
) COMMENT '拓樸圖節點表';

-- ===========================
-- VM 伺服器配置模組
-- ===========================

-- VM 伺服器表
CREATE TABLE vm_servers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hostname VARCHAR(100) NOT NULL COMMENT '主機名稱',
    ip_address VARCHAR(45) NOT NULL COMMENT 'IP 位址',
    os_type VARCHAR(50) COMMENT '作業系統類型',
    os_version VARCHAR(100) COMMENT '作業系統版本',
    cpu_cores INT COMMENT 'CPU 核心數',
    memory_gb INT COMMENT '記憶體 (GB)',
    storage_gb INT COMMENT '儲存空間 (GB)',
    hypervisor VARCHAR(50) COMMENT '虛擬化平台',
    host_server VARCHAR(100) COMMENT '實體主機',
    purpose TEXT COMMENT '用途說明',
    owner VARCHAR(50) COMMENT '負責人',
    status ENUM('running', 'stopped', 'maintenance') DEFAULT 'running' COMMENT '狀態',
    backup_enabled BOOLEAN DEFAULT FALSE COMMENT '是否啟用備份',
    backup_schedule VARCHAR(100) COMMENT '備份排程',
    monitoring_enabled BOOLEAN DEFAULT TRUE COMMENT '是否啟用監控',
    notes TEXT COMMENT '備註',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip_address (ip_address),
    INDEX idx_hostname (hostname),
    INDEX idx_status (status)
) COMMENT 'VM 伺服器表';

-- ===========================
-- 帳號資訊管理
-- ===========================

-- 系統帳號表
CREATE TABLE system_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    system_name VARCHAR(100) NOT NULL COMMENT '系統名稱',
    system_type ENUM('internal', 'cloud', 'third_party') DEFAULT 'internal' COMMENT '系統類型',
    url VARCHAR(500) COMMENT '系統網址',
    username VARCHAR(100) COMMENT '用戶名',
    email VARCHAR(100) COMMENT '電子郵件',
    password_encrypted TEXT COMMENT '加密密碼',
    purpose TEXT COMMENT '用途說明',
    department VARCHAR(50) COMMENT '使用部門',
    responsible_person VARCHAR(50) COMMENT '負責人',
    last_password_change DATE COMMENT '最後密碼變更日期',
    password_expiry_date DATE COMMENT '密碼到期日期',
    two_factor_enabled BOOLEAN DEFAULT FALSE COMMENT '是否啟用雙重驗證',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT '狀態',
    notes TEXT COMMENT '備註',
    created_by INT COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_system_name (system_name),
    INDEX idx_department (department)
) COMMENT '系統帳號表';

-- ===========================
-- 定期付費管理
-- ===========================

-- 定期付費項目表
CREATE TABLE recurring_payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(100) NOT NULL COMMENT '服務名稱',
    vendor VARCHAR(100) NOT NULL COMMENT '供應商',
    category VARCHAR(50) COMMENT '分類',
    amount DECIMAL(10, 2) NOT NULL COMMENT '金額',
    currency VARCHAR(3) DEFAULT 'TWD' COMMENT '幣別',
    billing_cycle ENUM('monthly', 'quarterly', 'semi_annual', 'annual') NOT NULL COMMENT '計費週期',
    start_date DATE NOT NULL COMMENT '開始日期',
    end_date DATE COMMENT '結束日期',
    next_payment_date DATE NOT NULL COMMENT '下次付款日期',
    auto_renewal BOOLEAN DEFAULT TRUE COMMENT '自動續約',
    payment_method VARCHAR(50) COMMENT '付款方式',
    contract_number VARCHAR(100) COMMENT '合約編號',
    description TEXT COMMENT '服務描述',
    department VARCHAR(50) COMMENT '使用部門',
    responsible_person VARCHAR(50) COMMENT '負責人',
    status ENUM('active', 'suspended', 'cancelled') DEFAULT 'active' COMMENT '狀態',
    reminder_days INT DEFAULT 30 COMMENT '提醒天數',
    notes TEXT COMMENT '備註',
    created_by INT COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_next_payment_date (next_payment_date),
    INDEX idx_status (status)
) COMMENT '定期付費項目表';

-- 付費記錄表
CREATE TABLE payment_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recurring_payment_id INT NOT NULL,
    payment_date DATE NOT NULL COMMENT '付款日期',
    amount DECIMAL(10, 2) NOT NULL COMMENT '付款金額',
    currency VARCHAR(3) DEFAULT 'TWD' COMMENT '幣別',
    payment_method VARCHAR(50) COMMENT '付款方式',
    transaction_id VARCHAR(100) COMMENT '交易編號',
    receipt_path VARCHAR(500) COMMENT '收據路徑',
    notes TEXT COMMENT '備註',
    created_by INT COMMENT '記錄者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recurring_payment_id) REFERENCES recurring_payments(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payment_date (payment_date),
    INDEX idx_recurring_payment_id (recurring_payment_id)
) COMMENT '付費記錄表';

-- ===========================
-- 追蹤碼與 GTM 管理
-- ===========================

-- 追蹤碼管理表
CREATE TABLE tracking_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_name VARCHAR(100) NOT NULL COMMENT '網站名稱',
    site_url VARCHAR(500) NOT NULL COMMENT '網站網址',
    ga_tracking_id VARCHAR(50) COMMENT 'GA 追蹤 ID',
    gtm_container_id VARCHAR(50) COMMENT 'GTM 容器 ID',
    fb_pixel_id VARCHAR(50) COMMENT 'Facebook Pixel ID',
    other_codes JSON COMMENT '其他追蹤碼 (JSON)',
    deployment_status ENUM('not_deployed', 'deployed', 'error') DEFAULT 'not_deployed' COMMENT '部署狀態',
    last_checked DATETIME COMMENT '最後檢查時間',
    check_result TEXT COMMENT '檢查結果',
    responsible_person VARCHAR(50) COMMENT '負責人',
    department VARCHAR(50) COMMENT '部門',
    notes TEXT COMMENT '備註',
    created_by INT COMMENT '建立者',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_site_name (site_name),
    INDEX idx_deployment_status (deployment_status)
) COMMENT '追蹤碼管理表';

-- ===========================
-- 系統日誌
-- ===========================

-- 操作日誌表
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT COMMENT '用戶 ID',
    action VARCHAR(100) NOT NULL COMMENT '操作動作',
    module VARCHAR(50) NOT NULL COMMENT '模組名稱',
    target_type VARCHAR(50) COMMENT '目標類型',
    target_id INT COMMENT '目標 ID',
    old_data JSON COMMENT '舊數據',
    new_data JSON COMMENT '新數據',
    ip_address VARCHAR(45) COMMENT 'IP 位址',
    user_agent TEXT COMMENT 'User Agent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_module (module),
    INDEX idx_created_at (created_at)
) COMMENT '操作日誌表';

-- ===========================
-- 系統設定
-- ===========================

-- 系統設定表
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL COMMENT '設定鍵',
    setting_value TEXT COMMENT '設定值',
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string' COMMENT '設定類型',
    description TEXT COMMENT '設定說明',
    is_public BOOLEAN DEFAULT FALSE COMMENT '是否為公開設定',
    updated_by INT COMMENT '更新者',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) COMMENT '系統設定表';

SET FOREIGN_KEY_CHECKS = 1;

-- ===========================
-- 初始數據
-- ===========================

-- 插入預設管理員帳號 (密碼: password)
INSERT INTO users (username, email, password_hash, full_name, department, position, role, status) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系統管理員', 'IT部', '系統管理員', 'admin', 'active'),
('mis_user', 'mis@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'MIS專員', 'IT部', 'MIS專員', 'user', 'active');

-- 插入設備分類
INSERT INTO equipment_categories (name, description, sort_order) VALUES
('網路設備', '路由器、交換器、防火牆等網路相關設備', 1),
('伺服器', '實體及虛擬伺服器', 2),
('個人電腦', '桌上型電腦、筆記型電腦', 3),
('周邊設備', '印表機、掃描器、投影機等', 4),
('儲存設備', 'NAS、硬碟、磁帶機等', 5);

-- 插入系統設定
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'IT資產與報修管理系統', 'string', '系統名稱', TRUE),
('site_logo', '/assets/images/logo.png', 'string', '系統 Logo 路徑', TRUE),
('maintenance_mode', 'false', 'boolean', '維護模式', FALSE),
('email_notifications', 'true', 'boolean', '啟用郵件通知', FALSE),
('default_language', 'zh-TW', 'string', '預設語言', TRUE),
('session_timeout', '3600', 'number', 'Session 超時時間 (秒)', FALSE);
