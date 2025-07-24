# IT 資產與報修管理系統 - 使用說明書

## 📋 目錄

1. [系統概述](#系統概述)
2. [環境需求](#環境需求)
3. [安裝部署](#安裝部署)
4. [系統配置](#系統配置)
5. [後端 API 使用](#後端-api-使用)
6. [前端使用指南](#前端使用指南)
7. [功能模組說明](#功能模組說明)
8. [開發指南](#開發指南)
9. [部署與維護](#部署與維護)
10. [故障排除](#故障排除)

---

## 系統概述

### 📌 專案背景
本系統是為解決 MIS 設備資訊管理分散、Excel 紀錄不便查詢等問題而開發。整合了設備管理、報修系統、資訊安全佈達等功能，提供完整的內部管理解決方案。

### 🎯 主要功能
- **資產設備管理**：設備登錄、分類管理、Excel 匯入匯出
- **報修系統**：故障報修、工單指派、維修追蹤
- **安全佈達**：公告發送、閱讀追蹤、簽閱確認
- **圖片管理**：支援拖拽上傳、貼上功能
- **權限控制**：管理員與一般用戶角色管理

### 🏗️ 技術架構
- **前端**：Vue 3 + TailwindCSS + Pinia
- **後端**：PHP + MySQL
- **認證**：JWT Token
- **檔案處理**：圖片上傳、縮圖生成、Excel 處理

---

## 環境需求

### 服務器要求
- **作業系統**：Windows Server 2016+ / Linux (Ubuntu 18.04+)
- **Web 服務器**：Apache 2.4+ / Nginx 1.16+
- **PHP 版本**：PHP 7.4+ (建議 PHP 8.0+)
- **資料庫**：MySQL 5.7+ / MariaDB 10.3+
- **記憶體**：最少 2GB RAM (建議 4GB+)
- **儲存空間**：最少 10GB (視檔案上傳量而定)

### PHP 擴展需求
```bash
# 必要擴展
php-mysql
php-gd
php-fileinfo
php-mbstring
php-json
php-zip

# 建議擴展
php-redis (快取)
php-imagick (圖片處理)
```

### 開發環境
- **Node.js**：16.0+ (前端開發)
- **npm/yarn**：最新版本
- **Composer**：2.0+ (如使用)
- **Git**：版本控制

---

## 安裝部署

### 🔧 後端安裝

#### 1. 下載專案
```bash
git clone [repository-url]
cd MIS/backend

php -S 0.0.0.0:8000 -t public
```

#### 2. 建立資料庫
```sql
-- 登入 MySQL
mysql -u root -p

-- 執行資料庫建立腳本
source database.sql
```

#### 3. 配置環境變數
```bash
# 複製環境變數範本
cp .env.example .env

# 編輯 .env 檔案
nano .env
```

#### 4. .env 配置範例
```env
# 應用設定
APP_NAME="IT資產與報修管理系統"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost

# 資料庫設定
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=mis_system
DB_USERNAME=root
DB_PASSWORD=your_password

# JWT 設定
JWT_SECRET=your-very-secure-secret-key-here
JWT_EXPIRE=3600

# 郵件設定
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@company.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@company.com
MAIL_FROM_NAME="MIS系統"

# 檔案上傳設定
UPLOAD_MAX_SIZE=5242880
```

#### 5. 設定目錄權限
```bash
# Linux/macOS
chmod -R 755 backend/
chmod -R 777 backend/uploads/
chmod -R 777 backend/logs/

# Windows (使用管理員權限)
icacls backend\uploads /grant Everyone:F /T
icacls backend\logs /grant Everyone:F /T
```

#### 6. Apache 虛擬主機配置
```apache
<VirtualHost *:80>
    DocumentRoot "C:/path/to/MIS/backend/public"
    ServerName mis-api.local

    <Directory "C:/path/to/MIS/backend/public">
        AllowOverride All
        Require all granted

        # 重寫規則
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>

    # PHP 設定
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value max_execution_time 300
    php_value memory_limit 256M
</VirtualHost>
```

### 🎨 前端安裝

#### 1. 安裝依賴
```bash
cd MIS/frontend
npm install
```

#### 2. 配置環境變數
```bash
# 複製環境變數範本
cp .env.example .env

# 編輯環境變數
nano .env
```

#### 3. .env 配置
```env
# 開發環境
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=IT資產與報修管理系統
VITE_APP_VERSION=1.0.0

# 生產環境
# VITE_API_BASE_URL=https://your-domain.com/api
```

#### 4. 開發模式運行
```bash
npm run dev
# 訪問 http://localhost:3000
```

#### 5. 生產建置
```bash
npm run build
# 建置檔案在 dist/ 目錄
```

---

## 系統配置

### 🔐 初始管理員設定

#### 1. 預設管理員帳號
```
用戶名：admin
密碼：password
郵箱：admin@company.com
```

#### 2. 首次登入後請務必：
- 修改預設密碼
- 更新個人資料
- 設定系統參數

### 📁 目錄結構說明

```
MIS/
├── backend/                    # 後端 PHP 程式
│   ├── config/                # 配置檔案
│   │   ├── app.php           # 應用程式配置
│   │   └── database.php      # 資料庫配置
│   ├── src/                  # 原始碼
│   │   ├── Core/             # 核心類別
│   │   │   ├── Database.php  # 資料庫連接
│   │   │   ├── Router.php    # 路由管理
│   │   │   ├── Request.php   # 請求處理
│   │   │   ├── Response.php  # 回應處理
│   │   │   └── JWT.php       # JWT 管理
│   │   ├── Controllers/      # 控制器
│   │   │   ├── AuthController.php        # 認證控制器
│   │   │   ├── EquipmentController.php   # 設備管理
│   │   │   ├── RepairController.php      # 報修管理
│   │   │   └── AnnouncementController.php # 公告管理
│   │   └── Middleware/       # 中介軟體
│   │       ├── AuthMiddleware.php        # 認證中介軟體
│   │       └── AdminMiddleware.php       # 管理員權限
│   ├── routes/               # 路由定義
│   │   └── api.php          # API 路由
│   ├── public/              # 公開目錄
│   │   └── index.php        # 入口檔案
│   ├── uploads/             # 上傳檔案
│   │   ├── images/          # 圖片檔案
│   │   ├── equipment/       # 設備圖片
│   │   └── announcements/   # 公告附件
│   └── logs/                # 日誌檔案
├── frontend/                # 前端 Vue.js 程式
│   ├── src/
│   │   ├── components/      # Vue 組件
│   │   ├── views/          # 頁面組件
│   │   ├── stores/         # Pinia 狀態管理
│   │   ├── router/         # Vue Router
│   │   ├── utils/          # 工具函數
│   │   └── assets/         # 靜態資源
│   ├── public/             # 公開檔案
│   └── dist/               # 建置輸出
├── database.sql            # 資料庫結構
├── CLAUDE.md              # Claude 開發指南
└── README.md              # 本檔案
```

---

## 後端 API 使用

### 🔑 認證系統

#### 登入
```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "password"
}
```

#### 回應
```json
{
  "success": true,
  "message": "登入成功",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "full_name": "系統管理員",
      "role": "admin"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600
  }
}
```

#### 使用 Token
```http
GET /api/equipment
Authorization: Bearer your-jwt-token-here
```

### 📦 API 端點列表

#### 認證相關
```http
POST   /api/auth/login           # 用戶登入
POST   /api/auth/logout          # 用戶登出
POST   /api/auth/refresh         # 刷新 Token
GET    /api/auth/me              # 獲取用戶資訊
POST   /api/auth/change-password # 修改密碼
PUT    /api/auth/profile         # 更新個人資料
```

#### 設備管理
```http
GET    /api/equipment            # 獲取設備列表
GET    /api/equipment/{id}       # 獲取單一設備
POST   /api/equipment            # 創建設備
PUT    /api/equipment/{id}       # 更新設備
DELETE /api/equipment/{id}       # 刪除設備
GET    /api/equipment/categories # 獲取設備分類
POST   /api/equipment/import     # 匯入設備
GET    /api/equipment/export     # 匯出設備
```

#### 報修管理
```http
GET    /api/repairs              # 獲取報修列表
GET    /api/repairs/{id}         # 獲取報修詳情
POST   /api/repairs              # 創建報修單
PUT    /api/repairs/{id}/status  # 更新狀態
POST   /api/repairs/{id}/comment # 添加評論
POST   /api/admin/repairs/{id}/assign # 指派工單(管理員)
GET    /api/repairs/statistics   # 獲取統計
```

#### 公告管理
```http
GET    /api/announcements        # 獲取公告列表
GET    /api/announcements/{id}   # 獲取公告詳情
POST   /api/announcements/{id}/acknowledge # 確認已讀
GET    /api/my/announcements     # 我的公告
POST   /api/admin/announcements  # 創建公告(管理員)
POST   /api/admin/announcements/{id}/send # 發送公告(管理員)
```

### 📄 API 回應格式

#### 成功回應
```json
{
  "success": true,
  "message": "操作成功",
  "data": { ... }
}
```

#### 分頁回應
```json
{
  "success": true,
  "message": "查詢成功",
  "data": [ ... ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "from": 1,
    "to": 20
  }
}
```

#### 錯誤回應
```json
{
  "success": false,
  "message": "錯誤訊息",
  "errors": {
    "field": ["驗證錯誤訊息"]
  }
}
```

---

## 前端使用指南

### 🚀 啟動前端

#### 開發模式
```bash
cd frontend
npm run dev
```
- 自動重載
- 熱更新
- 開發工具支援
- 訪問：http://localhost:40000

#### 生產建置
```bash
npm run build
npm run preview  # 預覽建置結果
```

### 📱 頁面結構

#### 主要頁面
- `/login` - 登入頁面
- `/dashboard` - 儀表板
- `/equipment` - 設備管理
- `/repairs` - 報修管理
- `/announcements` - 公告管理
- `/profile` - 個人資料

#### 管理員專用
- `/admin/users` - 用戶管理
- `/admin/settings` - 系統設定
- `/admin/logs` - 系統日誌

### 🎨 UI 組件使用

#### 按鈕組件
```vue
<template>
  <!-- 主要按鈕 -->
  <button class="btn btn-primary">確定</button>

  <!-- 次要按鈕 -->
  <button class="btn btn-secondary">取消</button>

  <!-- 危險按鈕 -->
  <button class="btn btn-danger">刪除</button>

  <!-- 輪廓按鈕 -->
  <button class="btn btn-outline">編輯</button>
</template>
```

#### 表單組件
```vue
<template>
  <div class="form-group">
    <label class="form-label">設備名稱</label>
    <input
      type="text"
      v-model="form.name"
      class="form-control"
      placeholder="請輸入設備名稱"
    />
    <div class="form-error" v-if="errors.name">
      {{ errors.name }}
    </div>
  </div>
</template>
```

#### 卡片組件
```vue
<template>
  <div class="card">
    <div class="card-header">
      <h3>設備資訊</h3>
    </div>
    <div class="card-body">
      <!-- 內容 -->
    </div>
    <div class="card-footer">
      <!-- 操作按鈕 -->
    </div>
  </div>
</template>
```

### 📡 API 呼叫範例

#### 使用 Axios
```javascript
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

// 設定攔截器
axios.interceptors.request.use(config => {
  const authStore = useAuthStore()
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
})

// API 呼叫
const fetchEquipment = async () => {
  try {
    const response = await axios.get('/api/equipment')
    return response.data
  } catch (error) {
    console.error('API Error:', error)
    throw error
  }
}
```

#### 使用 Pinia Store
```javascript
// stores/equipment.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useEquipmentStore = defineStore('equipment', {
  state: () => ({
    equipment: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchEquipment() {
      this.loading = true
      try {
        const response = await axios.get('/api/equipment')
        this.equipment = response.data.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    }
  }
})
```

---

## 功能模組說明

### 🔧 設備管理模組

#### 功能特色
- **設備登錄**：基本資訊、規格、圖片
- **分類管理**：階層式分類系統
- **批次操作**：Excel 匯入匯出
- **進階搜尋**：多條件過濾
- **狀態管理**：使用中、維修中、已淘汰

#### 使用流程
1. **新增設備**
   - 點擊「新增設備」按鈕
   - 填寫基本資訊（名稱、IP、財產編號等）
   - 上傳設備圖片
   - 設定設備分類和狀態
   - 儲存設備資訊

2. **批次匯入**
   - 下載 Excel 範本
   - 填寫設備資料
   - 上傳 Excel 檔案
   - 檢查匯入結果

3. **設備維護**
   - 查看設備列表
   - 篩選和搜尋設備
   - 編輯設備資訊
   - 更新設備狀態

### 🔨 報修管理模組

#### 工作流程
```
員工報修 → 管理員指派 → 技術人員處理 → 完成維修
```

#### 狀態說明
- **待處理**：剛提交的報修單
- **已指派**：已分配給技術人員
- **處理中**：正在維修
- **已完成**：維修完成
- **已取消**：取消報修

#### 使用流程
1. **提交報修**
   - 選擇故障設備
   - 描述故障情況
   - 上傳故障圖片
   - 設定優先等級
   - 提交報修單

2. **指派工單**（管理員）
   - 查看報修列表
   - 選擇技術人員
   - 設定預計完成時間
   - 指派工單

3. **處理報修**（技術人員）
   - 接收指派通知
   - 更新處理狀態
   - 記錄維修過程
   - 上傳解決方案圖片
   - 完成維修

### 📢 安全佈達模組

#### 功能特色
- **目標設定**：全體、部門、指定用戶
- **排程發送**：立即或預約發送
- **閱讀追蹤**：開信率統計
- **簽閱確認**：重要公告確認機制

#### 使用流程
1. **創建公告**（管理員）
   - 填寫公告標題和內容
   - 設定重要等級
   - 選擇發送對象
   - 設定是否需要簽閱
   - 選擇發送方式

2. **發送公告**
   - 立即發送或排程發送
   - 系統自動發送郵件通知
   - 記錄發送狀態

3. **閱讀確認**（一般用戶）
   - 接收公告通知
   - 點擊閱讀公告
   - 簽閱確認（如需要）

---

## 開發指南

### 🛠️ 後端開發

#### 新增 API 端點
1. **創建控制器**
```php
// src/Controllers/ExampleController.php
<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class ExampleController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $data = $this->db->fetchAll("SELECT * FROM example_table");
        Response::success($data);
    }
}
```

2. **註冊路由**
```php
// routes/api.php
$router->get('/examples', 'ExampleController@index');
```

#### 資料庫操作
```php
// 查詢
$data = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
$list = $this->db->fetchAll("SELECT * FROM users");

// 插入
$id = $this->db->insert('users', [
    'username' => 'john',
    'email' => 'john@example.com'
]);

// 更新
$this->db->update('users', [
    'full_name' => 'John Doe'
], 'id = ?', [$id]);

// 刪除
$this->db->delete('users', 'id = ?', [$id]);

// 分頁
$result = $this->db->paginate($sql, $params, $page, $perPage);
```

### 🎨 前端開發

#### 創建新頁面
1. **創建 Vue 組件**
```vue
<!-- src/views/ExamplePage.vue -->
<template>
  <div class="container mx-auto px-4 py-6">
    <div class="card">
      <div class="card-header">
        <h1 class="text-xl font-semibold">範例頁面</h1>
      </div>
      <div class="card-body">
        <!-- 頁面內容 -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const data = ref([])
const loading = ref(false)

const fetchData = async () => {
  loading.value = true
  try {
    // API 呼叫
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
```

2. **註冊路由**
```javascript
// src/router/index.js
{
  path: '/example',
  name: 'Example',
  component: () => import('@/views/ExamplePage.vue'),
  meta: { requiresAuth: true }
}
```

#### 狀態管理
```javascript
// src/stores/example.js
import { defineStore } from 'pinia'

export const useExampleStore = defineStore('example', {
  state: () => ({
    items: [],
    loading: false,
    error: null
  }),

  getters: {
    itemCount: (state) => state.items.length
  },

  actions: {
    async fetchItems() {
      this.loading = true
      try {
        const response = await api.get('/examples')
        this.items = response.data
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    }
  }
})
```

### 🔍 除錯技巧

#### 後端除錯
```php
// 開啟錯誤顯示
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 記錄到日誌
error_log('Debug info: ' . print_r($data, true));

// 使用 Response 除錯
Response::success(['debug' => $debugData]);
```

#### 前端除錯
```javascript
// Console 除錯
console.log('Debug:', data)
console.table(arrayData)

// Vue DevTools
// 安裝 Vue DevTools 瀏覽器擴展

// 網路請求除錯
axios.interceptors.response.use(
  response => {
    console.log('API Response:', response)
    return response
  },
  error => {
    console.error('API Error:', error)
    return Promise.reject(error)
  }
)
```

---

## 部署與維護

### 🚀 生產環境部署

#### 1. 伺服器準備
```bash
# 更新系統
sudo apt update && sudo apt upgrade -y

# 安裝必要軟體
sudo apt install apache2 mysql-server php8.0 php8.0-mysql php8.0-gd php8.0-mbstring

# 啟用 Apache 模組
sudo a2enmod rewrite
sudo a2enmod ssl
```

#### 2. 資料庫設定
```sql
-- 創建生產資料庫
CREATE DATABASE mis_system_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 創建專用用戶
CREATE USER 'mis_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON mis_system_prod.* TO 'mis_user'@'localhost';
FLUSH PRIVILEGES;
```

#### 3. SSL 證書設定
```bash
# 使用 Let's Encrypt
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

#### 4. 效能優化
```apache
# Apache 配置優化
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript text/xml application/xml
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/javascript "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 🔄 備份策略

#### 資料庫備份
```bash
#!/bin/bash
# backup-database.sh

# 設定變數
DB_NAME="mis_system_prod"
DB_USER="mis_user"
DB_PASS="secure_password"
BACKUP_DIR="/var/backups/mis"
DATE=$(date +%Y%m%d_%H%M%S)

# 創建備份目錄
mkdir -p $BACKUP_DIR

# 備份資料庫
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# 壓縮備份檔案
gzip $BACKUP_DIR/db_backup_$DATE.sql

# 保留最近 30 天的備份
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +30 -delete

echo "Database backup completed: db_backup_$DATE.sql.gz"
```

#### 檔案備份
```bash
#!/bin/bash
# backup-files.sh

# 備份上傳檔案
rsync -av --delete /var/www/mis/backend/uploads/ /var/backups/mis/uploads/

# 備份設定檔案
cp /var/www/mis/backend/.env /var/backups/mis/config/env_$(date +%Y%m%d)
```

#### 自動備份設定
```bash
# 編輯 crontab
crontab -e

# 添加備份排程
# 每天凌晨 2 點備份資料庫
0 2 * * * /path/to/backup-database.sh

# 每天凌晨 3 點備份檔案
0 3 * * * /path/to/backup-files.sh
```

### 📊 監控與日誌

#### 系統監控
```bash
# 查看系統資源
htop
df -h
free -h

# 查看 Apache 狀態
sudo systemctl status apache2

# 查看 MySQL 狀態
sudo systemctl status mysql
```

#### 日誌管理
```bash
# Apache 日誌
tail -f /var/log/apache2/access.log
tail -f /var/log/apache2/error.log

# MySQL 日誌
tail -f /var/log/mysql/error.log

# 應用程式日誌
tail -f /var/www/mis/backend/logs/app.log
```

#### 效能監控
```bash
# 安裝監控工具
sudo apt install netstat iotop

# 監控網路連接
netstat -tuln

# 監控磁碟 I/O
iotop

# 監控 MySQL 效能
mysql -u root -p -e "SHOW PROCESSLIST;"
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"
```

---

## 故障排除

### 🔧 常見問題

#### 後端問題

**問題 1：500 內部伺服器錯誤**
```bash
# 檢查錯誤日誌
tail -f /var/log/apache2/error.log

# 檢查 PHP 錯誤
tail -f /var/log/php/error.log

# 檢查檔案權限
ls -la /var/www/mis/backend/
chmod -R 755 /var/www/mis/backend/
chmod -R 777 /var/www/mis/backend/uploads/
```

**問題 2：資料庫連接失敗**
```bash
# 檢查 MySQL 服務
sudo systemctl status mysql

# 測試資料庫連接
mysql -u mis_user -p mis_system_prod

# 檢查 .env 配置
cat /var/www/mis/backend/.env
```

**問題 3：JWT Token 錯誤**
```php
// 檢查 JWT 密鑰設定
echo $_ENV['JWT_SECRET'];

// 重新生成密鑰
$key = bin2hex(random_bytes(32));
echo $key;
```

#### 前端問題

**問題 1：API 呼叫失敗**
```javascript
// 檢查網路請求
// 打開瀏覽器開發者工具 > Network

// 檢查 CORS 設定
// 確認後端已設定正確的 CORS 標頭

// 檢查 API 基礎 URL
console.log(import.meta.env.VITE_API_BASE_URL)
```

**問題 2：建置失敗**
```bash
# 清除 node_modules
rm -rf node_modules package-lock.json
npm install

# 檢查 Node.js 版本
node --version
npm --version

# 使用除錯模式建置
npm run build -- --debug
```

**問題 3：頁面載入緩慢**
```bash
# 分析包大小
npm run build -- --analyze

# 檢查網路載入
# 瀏覽器開發者工具 > Network > Slow 3G

# 啟用快取
# 確認 Apache/Nginx 快取設定
```

### 📋 效能優化

#### 後端優化
```php
// 啟用 OPcache
; php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000

// 資料庫索引優化
CREATE INDEX idx_equipment_ip ON equipment(ip_address);
CREATE INDEX idx_repair_status ON repair_requests(status);
CREATE INDEX idx_announcement_target ON security_announcements(target_type);

// 查詢優化
// 使用 EXPLAIN 分析查詢
EXPLAIN SELECT * FROM equipment WHERE status = 'active';
```

#### 前端優化
```javascript
// 延遲載入路由
const routes = [
  {
    path: '/equipment',
    component: () => import('@/views/Equipment.vue')
  }
]

// 圖片優化
// 使用 WebP 格式
// 實作圖片延遲載入
const useImageLazyLoading = () => {
  // 實作邏輯
}

// 快取策略
// service worker 快取
// localStorage 暫存
```

### 🔒 安全性檢查清單

#### 伺服器安全
- [ ] 更新作業系統和軟體
- [ ] 設定防火牆規則
- [ ] 禁用不必要的服務
- [ ] 設定 SSL/TLS 憑證
- [ ] 定期更改管理員密碼

#### 應用程式安全
- [ ] 使用強密碼策略
- [ ] 實作 CSRF 保護
- [ ] 驗證所有用戶輸入
- [ ] 設定檔案上傳限制
- [ ] 定期更新相依套件

#### 資料庫安全
- [ ] 使用專用資料庫用戶
- [ ] 定期備份資料庫
- [ ] 加密敏感資料
- [ ] 限制資料庫存取
- [ ] 監控異常查詢

---

## 📞 技術支援

### 聯絡資訊
- **技術負責人**：MIS 團隊
- **緊急聯絡**：[緊急聯絡電話]
- **郵箱支援**：mis-support@company.com

### 文件更新
- **版本**：1.0.0
- **更新日期**：2024-12-01
- **下次檢查**：2025-06-01

### 相關資源
- [PHP 官方文檔](https://www.php.net/docs.php)
- [Vue.js 官方文檔](https://vuejs.org/)
- [TailwindCSS 文檔](https://tailwindcss.com/docs)
- [MySQL 文檔](https://dev.mysql.com/doc/)

---

**⚠️ 重要提醒：**
1. 定期更新系統和相依套件
2. 保持備份策略的有效性
3. 監控系統效能和安全性
4. 記錄所有系統變更
5. 培訓用戶正確使用系統

**🆘 緊急狀況處理：**
1. 立即聯絡技術負責人
2. 保存錯誤訊息和日誌
3. 不要隨意修改生產環境
4. 準備系統回復計劃
