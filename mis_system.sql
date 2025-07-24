-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025-07-23 16:58:57
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `mis_system`
--

-- --------------------------------------------------------

--
-- 資料表結構 `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT '設備名稱',
  `category_id` int(11) DEFAULT NULL COMMENT '設備分類ID',
  `brand` varchar(100) DEFAULT NULL COMMENT '品牌',
  `model` varchar(100) DEFAULT NULL COMMENT '型號',
  `serial_number` varchar(100) DEFAULT NULL COMMENT '序號',
  `property_number` varchar(100) DEFAULT NULL COMMENT '財產編號',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP位址',
  `mac_address` varchar(17) DEFAULT NULL COMMENT 'MAC位址',
  `location` varchar(255) DEFAULT NULL COMMENT '擺放位置',
  `office_location` enum('taipei','changhua') DEFAULT 'taipei' COMMENT '辦公地點',
  `responsible_user_id` int(11) DEFAULT NULL COMMENT '負責人ID',
  `purchase_date` date DEFAULT NULL COMMENT '購買日期',
  `warranty_expiry` date DEFAULT NULL COMMENT '保固到期日',
  `status` enum('active','inactive','maintenance','retired') DEFAULT 'active' COMMENT '設備狀態',
  `notes` text DEFAULT NULL COMMENT '備註',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='設備資產表';

-- --------------------------------------------------------

--
-- 資料表結構 `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '分類名稱',
  `description` text DEFAULT NULL COMMENT '分類描述',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '建立時間',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT '更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='設備分類表';

--
-- 傾印資料表的資料 `equipment_categories`
--

INSERT INTO `equipment_categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, '桌上型電腦', '辦公室桌上型電腦設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(2, '筆記型電腦', '可攜式筆記型電腦設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(3, '伺服器', '伺服器設備，包含各種類型的伺服器', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(4, '螢幕/顯示器', '各種尺寸的顯示器設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(5, '印表機', '列印設備，包含雷射、噴墨等類型', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(6, '掃描器', '文件掃描設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(7, '網路設備', '一般網路相關設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(8, '交換器(Switch)', '網路交換器設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(9, '路由器(Router)', '網路路由器設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(10, '無線基地台(AP)', '無線網路基地台設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(11, '防火牆', '網路安全防火牆設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(12, 'UPS不斷電系統', '不斷電系統設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(13, '儲存設備', '資料儲存相關設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(14, '投影機', '會議室投影設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(15, '電話設備', '辦公電話系統設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(16, '攝影機', '監控或會議攝影設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(17, '麥克風/音響', '音訊設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27'),
(18, '其他週邊設備', '其他未分類的週邊設備', '2025-07-23 13:12:27', '2025-07-23 13:12:27');

-- --------------------------------------------------------

--
-- 資料表結構 `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_data` text DEFAULT NULL,
  `new_data` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action`, `module`, `target_id`, `old_data`, `new_data`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"::1\",\"user_agent\":\"curl\\/8.10.1\"}', '::1', 'curl/8.10.1', '2025-07-23 12:35:16'),
(2, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-23 12:35:51'),
(3, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-23 12:36:53'),
(4, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-23 13:02:40'),
(5, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-23 13:29:41'),
(6, 3, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/138.0.0.0 Safari\\/537.36\"}', '192.168.0.234', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-23 14:31:49'),
(7, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:45:02'),
(8, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:46:40'),
(9, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:47:19'),
(10, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:50:15'),
(11, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:51:05'),
(12, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:51:40'),
(13, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:51:54'),
(14, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:52:40'),
(15, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:53:02'),
(16, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:54:21'),
(17, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:56:43'),
(18, 4, 'login', 'auth', NULL, NULL, '{\"ip\":\"192.168.0.234\",\"user_agent\":\"curl\\/8.10.1\"}', '192.168.0.234', 'curl/8.10.1', '2025-07-23 14:57:29');

-- --------------------------------------------------------

--
-- 資料表結構 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `office_location` enum('taipei','changhua') DEFAULT 'taipei' COMMENT '辦公地點',
  `phone` varchar(20) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL COMMENT '分機號碼',
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 傾印資料表的資料 `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `department`, `position`, `office_location`, `phone`, `extension`, `role`, `status`, `avatar`, `last_login`, `created_at`, `updated_at`) VALUES
(3, 'admin', 'admin@company.com', '$2y$10$8wpF7BMxJtIiG3mdahVP2uLuFHHEfRR6dNrfMnfKhWWCQ4bFZzihC', '系統管理員', 'IT部門', '系統管理員', 'taipei', NULL, '101', 'admin', 'active', NULL, '2025-07-23 14:31:49', '2025-07-23 12:34:06', '2025-07-23 14:53:08'),
(4, 'john.chen', 'john.chen@company.com', '$2y$10$3JfByxwjNqYeVHK5r7NfduCPzhK3zKu5SrNxxWE1uVn4976GKM.Ha', '陳志明', 'IT部門', '系統工程師', 'taipei', '02-1234-5678', '201', 'user', 'active', NULL, '2025-07-23 14:57:29', '2025-07-23 08:34:21', '2025-07-23 14:57:29'),
(5, 'mary.wang', 'mary.wang@company.com', '$2y$10$3Sb/bv6/IkEg2K6ERGlIuOA7oXAZxNcnWeCsVLUHLMcuk.JBOOk6.', '王美麗', '人事部', '人事專員', 'changhua', '02-1234-5679', '301', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(6, 'david.lin', 'david.lin@company.com', '$2y$10$rNkME7gCcrABtfotbIBXXuP3zTAYaFqsxsDQDaMiVgdLGwvUo805W', '林大衛', 'IT部門', '網路管理員', 'taipei', '02-1234-5680', '202', 'admin', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(7, 'sarah.liu', 'sarah.liu@company.com', '$2y$10$O0swpy0YIM.xq449s19fROsUxbJy7ZTVDFkXlZg5XnDa8UyB50zTS', '劉淑華', '財務部', '會計師', 'changhua', '02-1234-5681', '401', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(8, 'kevin.huang', 'kevin.huang@company.com', '$2y$10$fOXTi0xCTgqO86QyUegY8.r6wSQM1VRuyaqEbVJmuAcBpuxw1sofy', '黃凱文', '業務部', '業務經理', 'taipei', '02-1234-5682', '501', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(9, 'jenny.wu', 'jenny.wu@company.com', '$2y$10$bHpVaa0CeetTbHUh/wnw9eMrlkVP3/ASKuCihZyrGarLIYl4l3da.', '吳珍妮', '行銷部', '行銷專員', 'changhua', '02-1234-5683', '601', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(10, 'michael.chang', 'eric.lai@paulaschoice.com.tw', '$2y$10$58H9.SnEmxjhOOexQsjZY.haLG0SMgXUzT2pMhy4k8CD6hZTOOHbq', '賴信偉', 'IT部門', '全端工程師', 'taipei', '04-44443333', '233', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 08:57:19'),
(11, 'linda.lee', 'linda.lee@company.com', '$2y$10$K125UPwEO/FGR5mD80slE.jFCvLByL5SeD7xLKoQ0gEiJLJSXIZfC', '李琳達', '客服部', '客服主管', 'changhua', '02-1234-5685', '701', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(12, 'alex.chen', 'alex.chen@company.com', '$2y$10$8pqMuCoTCVNHqI.gINYg0eg0T0SmRPVMV/SbrFRv88a3ZHZAqzUOm', '陳亞歷', '研發部', '研發工程師', 'taipei', '02-1234-5686', '801', 'user', 'active', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40'),
(13, 'jessica.tsai', 'jessica.tsai@company.com', '$2y$10$l9h1sNvotj7soyjH67195u.fDTio9xr09hpTa85vhwhyEJ0LFgEfm', '蔡潔西卡', '法務部', '法務專員', 'changhua', '02-1234-5687', '901', 'user', 'inactive', NULL, NULL, '2025-07-23 08:34:21', '2025-07-23 14:35:40');

-- --------------------------------------------------------

--
-- 資料表結構 `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用戶認證Token黑名單表';

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD UNIQUE KEY `property_number` (`property_number`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_responsible_user` (`responsible_user_id`),
  ADD KEY `idx_office_location` (`office_location`),
  ADD KEY `idx_status` (`status`);

--
-- 資料表索引 `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- 資料表索引 `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- 資料表索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 資料表索引 `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_token_hash` (`token_hash`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `equipment_categories` (`id`),
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`);

--
-- 資料表的限制式 `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- 資料表的限制式 `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
