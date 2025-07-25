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

-- 建立索引
CREATE INDEX idx_payments_item_number ON payments(item_number);
CREATE INDEX idx_payments_end_date ON payments(end_date);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_payment_method ON payments(payment_method);

-- 插入範例資料
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
