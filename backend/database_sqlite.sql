-- IT 資產與報修管理系統資料庫設計 (SQLite版本)
-- 建立日期: 2024-12-01

-- 用戶表
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    position VARCHAR(50),
    office_location VARCHAR(20) DEFAULT 'taipei' CHECK (office_location IN ('taipei', 'changhua')),
    extension VARCHAR(10),
    phone VARCHAR(20),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user', 'viewer')),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    avatar VARCHAR(255),
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- JWT Token 表
CREATE TABLE user_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 設備分類表
CREATE TABLE equipment_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#3B82F6',
    icon VARCHAR(50) DEFAULT 'computer',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 設備表
CREATE TABLE equipment (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    category_id INTEGER,
    brand VARCHAR(50),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    property_number VARCHAR(50),
    ip_address VARCHAR(45),
    mac_address VARCHAR(17),
    location VARCHAR(100),
    department VARCHAR(50),
    responsible_person VARCHAR(50),
    purchase_date DATE,
    warranty_end_date DATE,
    price DECIMAL(10,2),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'maintenance', 'decommissioned', 'lost')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES equipment_categories(id)
);

-- 報修請求表
CREATE TABLE repair_requests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    request_number VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    equipment_id INTEGER,
    location VARCHAR(100),
    priority VARCHAR(20) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'assigned', 'in_progress', 'resolved', 'closed', 'cancelled')),
    requester_id INTEGER NOT NULL,
    assigned_to INTEGER,
    estimated_completion DATETIME,
    actual_completion DATETIME,
    cost DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (requester_id) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- 資安公告表
CREATE TABLE security_announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority VARCHAR(20) DEFAULT 'medium' CHECK (priority IN ('low', 'medium', 'high', 'urgent')),
    category VARCHAR(50) DEFAULT 'general',
    status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published', 'archived')),
    author_id INTEGER NOT NULL,
    publish_date DATETIME,
    expire_date DATETIME,
    requires_acknowledgment BOOLEAN DEFAULT FALSE,
    attachment_path VARCHAR(255),
    read_count INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- 公告閱讀記錄表
CREATE TABLE announcement_reads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    announcement_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    acknowledged_at DATETIME,
    ip_address VARCHAR(45),
    FOREIGN KEY (announcement_id) REFERENCES security_announcements(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(announcement_id, user_id)
);

-- 系統日誌表
CREATE TABLE system_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    target_id INTEGER,
    old_data TEXT,
    new_data TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 系統設定表
CREATE TABLE system_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 定期付款管理表
CREATE TABLE payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_number INTEGER NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    vendor VARCHAR(100),
    purchase_url TEXT,
    account_info TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    billing_cycle VARCHAR(20) DEFAULT '年' CHECK (billing_cycle IN ('年', '月', '季', '半年')),
    payment_method VARCHAR(50) NOT NULL,
    amount_usd DECIMAL(10,2),
    amount_twd INTEGER,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'expired', 'cancelled')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 初始化資料

-- 插入預設管理員用戶 (密碼: password)
INSERT INTO users (username, email, password_hash, full_name, department, position, role) VALUES
('admin', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '系統管理員', 'IT部門', '系統管理員', 'admin');

-- 插入設備分類
INSERT INTO equipment_categories (name, description, color, icon) VALUES
('桌上型電腦', '辦公室使用的桌上型電腦', '#3B82F6', 'desktop-computer'),
('筆記型電腦', '移動辦公用筆記型電腦', '#10B981', 'laptop'),
('印表機', '各類印表機設備', '#F59E0B', 'printer'),
('伺服器', '機房伺服器設備', '#EF4444', 'server'),
('網路設備', '交換器、路由器等網路設備', '#8B5CF6', 'switch');

-- 插入系統設定
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'IT資產與報修管理系統', '網站名稱'),
('maintenance_mode', 'false', '維護模式'),
('notification_email', 'admin@company.com', '系統通知郵箱'),
('auto_assign_repairs', 'false', '自動分派報修單'),
('max_file_upload_size', '5242880', '最大檔案上傳大小(bytes)');

-- 建立付款表索引
CREATE INDEX idx_payments_item_number ON payments(item_number);
CREATE INDEX idx_payments_end_date ON payments(end_date);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_payment_method ON payments(payment_method);

-- 插入付款範例資料
INSERT INTO payments (
    item_number, name, vendor, purchase_url, account_info,
    start_date, end_date, billing_cycle, payment_method,
    amount_usd, amount_twd, status, notes
) VALUES
(10, 'paulaschoice.com.tw SSL憑證專用', 'SSL Dragon', 'https://www.ssldragon.com/', 'htaiwan@paulaschoice.com', '2025-01-30', '2026-01-31', '年', '個人先支出', 6701.00, NULL, 'active', 'IT資訊部'),
(11, 'paulaschoice.hk SSL憑證專用', 'SSL Dragon', 'https://www.ssldragon.com/', 'g*****s', '2024-10-10', '2027-10-10', '年', '個人先支出', NULL, NULL, 'active', 'IT資訊部'),
(12, 'Figma UI/UX工具', 'Figma', 'https://www.figma.com/', 'paulaschoicecpth@gmail.com\n=Paulaschoice5932=', '2025-06-09', '2026-06-08', '年', '我的中信卡', 192.00, 5757, 'active', 'Google(paulaschoicecpth) - IT資訊部'),
(13, 'PNGtree圖庫服務', 'PNGtree', 'https://zh.pngtree.com/', NULL, '2024-08-29', '2025-08-29', '年', '新光商務卡 5304', NULL, 19141, 'active', '商檢行銷部(美工)'),
(15, 'ApplePay憑證', 'Apple Developer', 'https://developer.apple.com/', NULL, '2024-11-04', '2025-11-05', '年', '新光商務卡 5304', NULL, 3400, 'active', 'ApplePay');
