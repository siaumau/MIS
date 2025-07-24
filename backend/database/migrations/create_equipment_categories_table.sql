-- 創建設備分類表
CREATE TABLE IF NOT EXISTS equipment_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT '分類名稱',
    description TEXT COMMENT '分類描述',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='設備分類表';

-- 插入預設的MIS硬體設備分類
INSERT INTO equipment_categories (name, description) VALUES
('桌上型電腦', '辦公室桌上型電腦設備'),
('筆記型電腦', '可攜式筆記型電腦設備'),
('伺服器', '伺服器設備，包含各種類型的伺服器'),
('螢幕/顯示器', '各種尺寸的顯示器設備'),
('印表機', '列印設備，包含雷射、噴墨等類型'),
('掃描器', '文件掃描設備'),
('網路設備', '一般網路相關設備'),
('交換器(Switch)', '網路交換器設備'),
('路由器(Router)', '網路路由器設備'),
('無線基地台(AP)', '無線網路基地台設備'),
('防火牆', '網路安全防火牆設備'),
('UPS不斷電系統', '不斷電系統設備'), 
('儲存設備', '資料儲存相關設備'),
('投影機', '會議室投影設備'),
('電話設備', '辦公電話系統設備'),
('攝影機', '監控或會議攝影設備'),
('麥克風/音響', '音訊設備'),
('其他週邊設備', '其他未分類的週邊設備');

-- 如果 equipment 表存在，為其添加 category_id 外鍵（如果還沒有的話）
-- ALTER TABLE equipment ADD COLUMN category_id INT NULL COMMENT '設備分類ID' AFTER name;
-- ALTER TABLE equipment ADD FOREIGN KEY (category_id) REFERENCES equipment_categories(id) ON DELETE SET NULL;