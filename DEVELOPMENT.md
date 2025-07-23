# 開發指南 - IT 資產與報修管理系統

## 📋 目錄

1. [開發環境設置](#開發環境設置)
2. [項目結構說明](#項目結構說明)
3. [後端開發指南](#後端開發指南)
4. [前端開發指南](#前端開發指南)
5. [API 開發規範](#api-開發規範)
6. [資料庫開發](#資料庫開發)
7. [測試指南](#測試指南)
8. [代碼規範](#代碼規範)
9. [Git 工作流程](#git-工作流程)
10. [除錯與優化](#除錯與優化)

---

## 開發環境設置

### 💻 必要軟體安裝

#### Windows 開發環境
```bash
# 安裝 Chocolatey (包管理器)
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

# 安裝開發工具
choco install git nodejs php mysql apache vscode

# 安裝 Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=bin --filename=composer
```

#### macOS 開發環境
```bash
# 安裝 Homebrew
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# 安裝開發工具
brew install git node php mysql httpd
brew install --cask visual-studio-code

# 安裝 Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

#### Linux (Ubuntu/Debian) 開發環境
```bash
# 更新套件列表
sudo apt update

# 安裝開發工具
sudo apt install -y git nodejs npm php8.0 php8.0-mysql php8.0-gd php8.0-mbstring php8.0-zip mysql-server apache2 curl

# 安裝 Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 安裝 VS Code
wget -qO- https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > packages.microsoft.gpg
sudo install -o root -g root -m 644 packages.microsoft.gpg /etc/apt/trusted.gpg.d/
sudo sh -c 'echo "deb [arch=amd64,arm64,armhf signed-by=/etc/apt/trusted.gpg.d/packages.microsoft.gpg] https://packages.microsoft.com/repos/code stable main" > /etc/apt/sources.list.d/vscode.list'
sudo apt update
sudo apt install code
```

### 🔧 IDE 配置

#### VS Code 推薦擴展
```json
{
  "recommendations": [
    "bmewburn.vscode-intelephense-client",
    "Vue.volar",
    "bradlc.vscode-tailwindcss",
    "ms-vscode.vscode-json",
    "esbenp.prettier-vscode",
    "dbaeumer.vscode-eslint",
    "formulahendry.auto-rename-tag",
    "christian-kohler.path-intellisense",
    "ms-vscode.vscode-typescript-next"
  ]
}
```

#### VS Code 設定檔案
```json
{
  "editor.formatOnSave": true,
  "editor.codeActionsOnSave": {
    "source.fixAll.eslint": true
  },
  "php.suggest.basic": false,
  "php.validate.enable": false,
  "intelephense.files.maxSize": 5000000,
  "vue.format.defaultFormatter": {
    "html": "prettier",
    "css": "prettier",
    "js": "prettier",
    "ts": "prettier"
  },
  "tailwindCSS.includeLanguages": {
    "vue": "html"
  }
}
```

### 🛠️ 開發服務器設置

#### XAMPP 快速設置 (Windows/macOS)
```bash
# 下載並安裝 XAMPP
# https://www.apachefriends.org/download.html

# 啟動 Apache 和 MySQL
# 通過 XAMPP 控制面板

# 配置虛擬主機
# 編輯 C:\xampp\apache\conf\extra\httpd-vhosts.conf
```

```apache
<VirtualHost *:80>
    DocumentRoot "C:/path/to/MIS/backend/public"
    ServerName mis-api.local
    <Directory "C:/path/to/MIS/backend/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "C:/path/to/MIS/frontend/dist"
    ServerName mis.local
    <Directory "C:/path/to/MIS/frontend/dist">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Laravel Valet 設置 (macOS)
```bash
# 安裝 Valet
composer global require laravel/valet
valet install

# 設置專案目錄
cd ~/Sites
valet park

# 連結專案
cd /path/to/MIS/backend
valet link mis-api

cd /path/to/MIS/frontend
valet link mis
```

---

## 項目結構說明

### 📁 整體目錄結構
```
MIS/
├── backend/                    # PHP 後端
│   ├── config/                # 配置檔案
│   │   ├── app.php           # 應用程式配置
│   │   └── database.php      # 資料庫配置
│   ├── src/                  # 源代碼
│   │   ├── Core/             # 核心類別
│   │   ├── Controllers/      # 控制器
│   │   ├── Middleware/       # 中介軟體
│   │   ├── Models/           # 資料模型 (未來擴展)
│   │   └── Services/         # 業務邏輯服務
│   ├── routes/               # 路由定義
│   ├── public/               # 公開目錄
│   ├── uploads/              # 檔案上傳
│   ├── logs/                 # 日誌檔案
│   ├── tests/                # 測試檔案
│   ├── .env.example          # 環境變數範本
│   └── composer.json         # PHP 依賴管理
├── frontend/                 # Vue.js 前端
│   ├── src/
│   │   ├── components/       # Vue 組件
│   │   │   ├── common/       # 通用組件
│   │   │   ├── forms/        # 表單組件
│   │   │   └── ui/           # UI 組件
│   │   ├── views/            # 頁面組件
│   │   │   ├── auth/         # 認證相關頁面
│   │   │   ├── equipment/    # 設備管理頁面
│   │   │   ├── repair/       # 報修管理頁面
│   │   │   └── announcement/ # 公告管理頁面
│   │   ├── stores/           # Pinia 狀態管理
│   │   ├── router/           # Vue Router
│   │   ├── composables/      # 組合式函數
│   │   ├── utils/            # 工具函數
│   │   ├── services/         # API 服務
│   │   └── assets/           # 靜態資源
│   ├── public/               # 公開檔案
│   ├── tests/                # 測試檔案
│   ├── .env.example          # 環境變數範本
│   ├── package.json          # Node.js 依賴管理
│   └── vite.config.js        # Vite 配置
├── docs/                     # 文檔
├── database.sql              # 資料庫結構
├── README.md                 # 主要說明文檔
├── DEPLOYMENT.md             # 部署指南
└── DEVELOPMENT.md            # 本文檔
```

---

## 後端開發指南

### 🐘 PHP 架構說明

#### 核心類別設計
```php
// Core/Database.php - 資料庫連接管理
// Core/Router.php - 路由管理
// Core/Request.php - HTTP 請求處理
// Core/Response.php - HTTP 回應處理
// Core/JWT.php - JWT Token 管理
```

#### MVC 模式實現
```
Controller -> Service -> Database
    ↓
Response (JSON)
```

### 📝 創建新的 API 端點

#### 1. 創建控制器
```php
<?php
// src/Controllers/ExampleController.php

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

    /**
     * 獲取列表
     */
    public function index(): void
    {
        $request = new Request();
        $page = (int)$request->input('page', 1);
        $perPage = min((int)$request->input('per_page', 20), 100);

        $sql = "SELECT * FROM example_table ORDER BY created_at DESC";
        $result = $this->db->paginate($sql, [], $page, $perPage);

        Response::paginated($result);
    }

    /**
     * 獲取單一記錄
     */
    public function show(array $params): void
    {
        $id = (int)$params['id'];
        $data = $this->db->fetch("SELECT * FROM example_table WHERE id = ?", [$id]);

        if (!$data) {
            Response::notFound('記錄不存在');
        }

        Response::success($data);
    }

    /**
     * 創建記錄
     */
    public function store(): void
    {
        $request = new Request();

        // 驗證輸入
        $errors = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only(['name', 'email', 'description']);
        $data['created_by'] = $GLOBALS['current_user']['id'];

        try {
            $id = $this->db->insert('example_table', $data);

            // 記錄日誌
            $this->logAction('create', 'example', $id, null, $data);

            Response::created(['id' => $id], '創建成功');
        } catch (\Exception $e) {
            Response::serverError('創建失敗：' . $e->getMessage());
        }
    }

    /**
     * 更新記錄
     */
    public function update(array $params): void
    {
        $id = (int)$params['id'];
        $request = new Request();

        $existing = $this->db->fetch("SELECT * FROM example_table WHERE id = ?", [$id]);
        if (!$existing) {
            Response::notFound('記錄不存在');
        }

        $errors = $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only(['name', 'email', 'description']);

        try {
            $this->db->update('example_table', $data, 'id = ?', [$id]);

            // 記錄日誌
            $this->logAction('update', 'example', $id, $existing, $data);

            Response::updated(null, '更新成功');
        } catch (\Exception $e) {
            Response::serverError('更新失敗：' . $e->getMessage());
        }
    }

    /**
     * 刪除記錄
     */
    public function destroy(array $params): void
    {
        $id = (int)$params['id'];

        $existing = $this->db->fetch("SELECT * FROM example_table WHERE id = ?", [$id]);
        if (!$existing) {
            Response::notFound('記錄不存在');
        }

        try {
            $this->db->delete('example_table', 'id = ?', [$id]);

            // 記錄日誌
            $this->logAction('delete', 'example', $id, $existing);

            Response::deleted('刪除成功');
        } catch (\Exception $e) {
            Response::serverError('刪除失敗：' . $e->getMessage());
        }
    }

    /**
     * 記錄操作日誌
     */
    private function logAction(string $action, string $module, int $targetId, ?array $oldData = null, ?array $newData = null): void
    {
        $request = new Request();

        $this->db->insert('system_logs', [
            'user_id' => $GLOBALS['current_user']['id'],
            'action' => $action,
            'module' => $module,
            'target_id' => $targetId,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }
}
```

#### 2. 註冊路由
```php
// routes/api.php

// 在認證路由群組中添加
$router->group(['middleware' => 'AuthMiddleware'], function($router) {
    // 其他路由...

    // 範例路由
    $router->get('/examples', 'ExampleController@index');
    $router->get('/examples/{id}', 'ExampleController@show');
    $router->post('/examples', 'ExampleController@store');
    $router->put('/examples/{id}', 'ExampleController@update');
    $router->delete('/examples/{id}', 'ExampleController@destroy');
});
```

### 🛡️ 中介軟體開發

#### 創建自定義中介軟體
```php
<?php
// src/Middleware/RateLimitMiddleware.php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class RateLimitMiddleware
{
    private int $maxRequests = 60; // 每分鐘最大請求數
    private int $timeWindow = 60;  // 時間窗口 (秒)

    public function handle(): void
    {
        $request = new Request();
        $ip = $request->ip();
        $key = "rate_limit:$ip";

        $db = Database::getInstance();

        // 檢查當前請求數
        $current = $this->getCurrentRequests($ip);

        if ($current >= $this->maxRequests) {
            Response::error('請求過於頻繁，請稍後再試', 429);
        }

        // 記錄請求
        $this->recordRequest($ip);
    }

    private function getCurrentRequests(string $ip): int
    {
        $db = Database::getInstance();
        $result = $db->fetch("
            SELECT COUNT(*) as count
            FROM request_logs
            WHERE ip_address = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ", [$ip, $this->timeWindow]);

        return (int)$result['count'];
    }

    private function recordRequest(string $ip): void
    {
        $db = Database::getInstance();
        $db->insert('request_logs', [
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 清理舊記錄
        $db->delete('request_logs', 'created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)', [$this->timeWindow * 2]);
    }
}
```

### 📊 服務層開發

#### 創建業務邏輯服務
```php
<?php
// src/Services/NotificationService.php

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 發送報修通知
     */
    public function sendRepairNotification(int $repairId, string $type): bool
    {
        $repair = $this->db->fetch("
            SELECT rr.*, u.full_name, u.email
            FROM repair_requests rr
            JOIN users u ON rr.requester_id = u.id
            WHERE rr.id = ?
        ", [$repairId]);

        if (!$repair) {
            return false;
        }

        switch ($type) {
            case 'created':
                return $this->notifyAdminsNewRepair($repair);
            case 'assigned':
                return $this->notifyAssignedTechnician($repair);
            case 'completed':
                return $this->notifyRequesterCompletion($repair);
            default:
                return false;
        }
    }

    /**
     * 發送公告通知
     */
    public function sendAnnouncementNotification(int $announcementId): bool
    {
        $announcement = $this->db->fetch("SELECT * FROM security_announcements WHERE id = ?", [$announcementId]);
        $targetUsers = $this->getAnnouncementTargets($announcement);

        foreach ($targetUsers as $user) {
            $this->sendEmail(
                $user['email'],
                '新安全公告：' . $announcement['title'],
                $this->buildAnnouncementEmailContent($announcement, $user)
            );
        }

        return true;
    }

    /**
     * 獲取公告目標用戶
     */
    private function getAnnouncementTargets(array $announcement): array
    {
        if ($announcement['target_type'] === 'all') {
            return $this->db->fetchAll("SELECT * FROM users WHERE status = 'active'");
        } elseif ($announcement['target_type'] === 'department') {
            $departments = json_decode($announcement['target_departments'], true);
            $placeholders = str_repeat('?,', count($departments) - 1) . '?';
            return $this->db->fetchAll(
                "SELECT * FROM users WHERE status = 'active' AND department IN ($placeholders)",
                $departments
            );
        } elseif ($announcement['target_type'] === 'users') {
            $userIds = json_decode($announcement['target_users'], true);
            $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
            return $this->db->fetchAll(
                "SELECT * FROM users WHERE status = 'active' AND id IN ($placeholders)",
                $userIds
            );
        }

        return [];
    }

    /**
     * 發送郵件
     */
    private function sendEmail(string $to, string $subject, string $body): bool
    {
        // 這裡實現實際的郵件發送邏輯
        // 可以使用 PHPMailer 或其他郵件服務

        $config = include __DIR__ . '/../../config/app.php';

        $headers = [
            'From: ' . $config['mail']['from']['address'],
            'Content-Type: text/html; charset=UTF-8'
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * 建構公告郵件內容
     */
    private function buildAnnouncementEmailContent(array $announcement, array $user): string
    {
        return "
        <html>
        <body>
            <h2>{$announcement['title']}</h2>
            <p>親愛的 {$user['full_name']}，</p>
            <p>您有一則新的安全公告需要查看：</p>
            <div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>
                {$announcement['content']}
            </div>
            <p>請點擊以下連結查看完整公告：</p>
            <a href='" . $_ENV['APP_URL'] . "/announcements/{$announcement['id']}'>查看公告</a>
            <p>此郵件由系統自動發送，請勿回覆。</p>
        </body>
        </html>
        ";
    }
}
```

---

## 前端開發指南

### 🎨 Vue.js 架構說明

#### 組件結構設計
```
components/
├── common/          # 通用組件
│   ├── AppHeader.vue
│   ├── AppSidebar.vue
│   ├── AppFooter.vue
│   └── AppBreadcrumb.vue
├── forms/           # 表單組件
│   ├── FormInput.vue
│   ├── FormSelect.vue
│   ├── FormTextarea.vue
│   └── FormUpload.vue
└── ui/              # UI 組件
    ├── Button.vue
    ├── Card.vue
    ├── Modal.vue
    ├── Table.vue
    └── Pagination.vue
```

### 🔧 創建新的頁面組件

#### 1. 頁面組件範本
```vue
<!-- src/views/ExamplePage.vue -->
<template>
  <div class="container mx-auto px-4 py-6">
    <!-- 頁面標題 -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">範例頁面</h1>
      <p class="text-gray-600 mt-1">頁面描述文字</p>
    </div>

    <!-- 操作按鈕 -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-2">
        <Button @click="handleCreate" variant="primary">
          <PlusIcon class="w-4 h-4 mr-2" />
          新增
        </Button>
        <Button @click="handleRefresh" variant="outline">
          <RefreshIcon class="w-4 h-4 mr-2" />
          重新整理
        </Button>
      </div>

      <!-- 搜尋框 -->
      <div class="w-96">
        <FormInput
          v-model="searchQuery"
          placeholder="搜尋..."
          @input="handleSearch"
        />
      </div>
    </div>

    <!-- 主要內容 -->
    <Card>
      <!-- 載入狀態 -->
      <div v-if="loading" class="flex justify-center py-12">
        <LoadingSpinner />
      </div>

      <!-- 錯誤狀態 -->
      <div v-else-if="error" class="text-center py-12">
        <ExclamationIcon class="mx-auto h-12 w-12 text-red-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">載入失敗</h3>
        <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
        <Button @click="fetchData" variant="primary" class="mt-4">
          重試
        </Button>
      </div>

      <!-- 資料表格 -->
      <div v-else>
        <Table
          :columns="columns"
          :data="items"
          :loading="loading"
          @sort="handleSort"
          @row-click="handleRowClick"
        />

        <!-- 分頁 -->
        <div class="mt-6">
          <Pagination
            :current-page="pagination.current_page"
            :total-pages="pagination.last_page"
            :total-items="pagination.total"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </Card>

    <!-- 新增/編輯模態框 -->
    <Modal v-model="showModal" :title="modalTitle" size="lg">
      <ExampleForm
        :item="selectedItem"
        @submit="handleSubmit"
        @cancel="closeModal"
      />
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import {
  PlusIcon,
  RefreshIcon,
  ExclamationIcon
} from '@heroicons/vue/24/outline'

// 組件引入
import Button from '@/components/ui/Button.vue'
import Card from '@/components/ui/Card.vue'
import Modal from '@/components/ui/Modal.vue'
import Table from '@/components/ui/Table.vue'
import Pagination from '@/components/ui/Pagination.vue'
import FormInput from '@/components/forms/FormInput.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ExampleForm from '@/components/forms/ExampleForm.vue'

// 服務和 Store
import { useExampleStore } from '@/stores/example'
import exampleService from '@/services/exampleService'

// 初始化
const router = useRouter()
const toast = useToast()
const exampleStore = useExampleStore()

// 響應式狀態
const loading = ref(false)
const error = ref(null)
const searchQuery = ref('')
const showModal = ref(false)
const selectedItem = ref(null)

// 表格配置
const columns = ref([
  { key: 'id', label: 'ID', sortable: true },
  { key: 'name', label: '名稱', sortable: true },
  { key: 'email', label: '郵箱', sortable: true },
  { key: 'created_at', label: '建立時間', sortable: true },
  { key: 'actions', label: '操作', width: '120px' }
])

// 計算屬性
const modalTitle = computed(() => {
  return selectedItem.value ? '編輯項目' : '新增項目'
})

const items = computed(() => exampleStore.items)
const pagination = computed(() => exampleStore.pagination)

// 方法
const fetchData = async (params = {}) => {
  loading.value = true
  error.value = null

  try {
    await exampleStore.fetchItems(params)
  } catch (err) {
    error.value = err.message
    toast.error('載入資料失敗')
  } finally {
    loading.value = false
  }
}

const handleCreate = () => {
  selectedItem.value = null
  showModal.value = true
}

const handleEdit = (item) => {
  selectedItem.value = { ...item }
  showModal.value = true
}

const handleDelete = async (item) => {
  if (!confirm('確定要刪除此項目嗎？')) return

  try {
    await exampleService.delete(item.id)
    await fetchData()
    toast.success('刪除成功')
  } catch (error) {
    toast.error('刪除失敗：' + error.message)
  }
}

const handleSubmit = async (formData) => {
  try {
    if (selectedItem.value) {
      await exampleService.update(selectedItem.value.id, formData)
      toast.success('更新成功')
    } else {
      await exampleService.create(formData)
      toast.success('創建成功')
    }

    closeModal()
    await fetchData()
  } catch (error) {
    toast.error('操作失敗：' + error.message)
  }
}

const closeModal = () => {
  showModal.value = false
  selectedItem.value = null
}

const handleRefresh = () => {
  fetchData()
}

const handleSearch = debounce(async (query) => {
  await fetchData({ search: query })
}, 300)

const handleSort = async (column, direction) => {
  await fetchData({
    sort: column,
    direction: direction
  })
}

const handleRowClick = (item) => {
  router.push(`/examples/${item.id}`)
}

const handlePageChange = async (page) => {
  await fetchData({ page })
}

// 防抖函數
function debounce(func, wait) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// 生命週期
onMounted(() => {
  fetchData()
})
</script>
```

#### 2. 表單組件範本
```vue
<!-- src/components/forms/ExampleForm.vue -->
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- 基本資訊 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <FormGroup>
        <FormLabel required>名稱</FormLabel>
        <FormInput
          v-model="form.name"
          :error="errors.name"
          placeholder="請輸入名稱"
          required
        />
      </FormGroup>

      <FormGroup>
        <FormLabel required>郵箱</FormLabel>
        <FormInput
          v-model="form.email"
          :error="errors.email"
          type="email"
          placeholder="請輸入郵箱"
          required
        />
      </FormGroup>
    </div>

    <!-- 描述 -->
    <FormGroup>
      <FormLabel>描述</FormLabel>
      <FormTextarea
        v-model="form.description"
        :error="errors.description"
        placeholder="請輸入描述"
        rows="4"
      />
    </FormGroup>

    <!-- 圖片上傳 -->
    <FormGroup>
      <FormLabel>圖片</FormLabel>
      <ImageUpload
        v-model="form.images"
        :max-files="5"
        :max-size="5 * 1024 * 1024"
        @upload="handleImageUpload"
      />
    </FormGroup>

    <!-- 操作按鈕 -->
    <div class="flex justify-end space-x-3 pt-6 border-t">
      <Button
        type="button"
        variant="outline"
        @click="$emit('cancel')"
      >
        取消
      </Button>
      <Button
        type="submit"
        variant="primary"
        :loading="submitting"
      >
        {{ isEdit ? '更新' : '創建' }}
      </Button>
    </div>
  </form>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useToast } from 'vue-toastification'

// 組件引入
import Button from '@/components/ui/Button.vue'
import FormGroup from '@/components/forms/FormGroup.vue'
import FormLabel from '@/components/forms/FormLabel.vue'
import FormInput from '@/components/forms/FormInput.vue'
import FormTextarea from '@/components/forms/FormTextarea.vue'
import ImageUpload from '@/components/forms/ImageUpload.vue'

// Props
const props = defineProps({
  item: {
    type: Object,
    default: null
  }
})

// Emits
const emit = defineEmits(['submit', 'cancel'])

// 狀態
const submitting = ref(false)
const toast = useToast()

// 表單資料
const form = reactive({
  name: '',
  email: '',
  description: '',
  images: []
})

// 錯誤狀態
const errors = reactive({
  name: '',
  email: '',
  description: ''
})

// 計算屬性
const isEdit = computed(() => !!props.item)

// 監聽 props 變化
watch(() => props.item, (newItem) => {
  if (newItem) {
    Object.assign(form, {
      name: newItem.name || '',
      email: newItem.email || '',
      description: newItem.description || '',
      images: newItem.images || []
    })
  } else {
    resetForm()
  }
}, { immediate: true })

// 方法
const handleSubmit = async () => {
  if (!validateForm()) return

  submitting.value = true

  try {
    emit('submit', { ...form })
  } catch (error) {
    toast.error('提交失敗：' + error.message)
  } finally {
    submitting.value = false
  }
}

const validateForm = () => {
  // 清空錯誤
  Object.keys(errors).forEach(key => errors[key] = '')

  let isValid = true

  // 名稱驗證
  if (!form.name.trim()) {
    errors.name = '名稱為必填項目'
    isValid = false
  } else if (form.name.length > 100) {
    errors.name = '名稱不能超過 100 個字符'
    isValid = false
  }

  // 郵箱驗證
  if (!form.email.trim()) {
    errors.email = '郵箱為必填項目'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = '請輸入有效的郵箱地址'
    isValid = false
  }

  return isValid
}

const resetForm = () => {
  Object.assign(form, {
    name: '',
    email: '',
    description: '',
    images: []
  })
  Object.keys(errors).forEach(key => errors[key] = '')
}

const handleImageUpload = (uploadedImages) => {
  form.images = [...form.images, ...uploadedImages]
}
</script>
```

### 🗂️ 狀態管理 (Pinia)

#### Store 範本
```javascript
// src/stores/example.js
import { defineStore } from 'pinia'
import exampleService from '@/services/exampleService'

export const useExampleStore = defineStore('example', {
  state: () => ({
    items: [],
    currentItem: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 20
    },
    loading: false,
    error: null,
    filters: {
      search: '',
      status: '',
      category: ''
    }
  }),

  getters: {
    // 過濾後的項目
    filteredItems: (state) => {
      let filtered = state.items

      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        filtered = filtered.filter(item =>
          item.name.toLowerCase().includes(search) ||
          item.email.toLowerCase().includes(search)
        )
      }

      if (state.filters.status) {
        filtered = filtered.filter(item => item.status === state.filters.status)
      }

      return filtered
    },

    // 項目數量統計
    itemStats: (state) => {
      const stats = {
        total: state.items.length,
        active: 0,
        inactive: 0
      }

      state.items.forEach(item => {
        if (item.status === 'active') stats.active++
        else stats.inactive++
      })

      return stats
    },

    // 是否有數據
    hasData: (state) => state.items.length > 0,

    // 是否正在載入
    isLoading: (state) => state.loading
  },

  actions: {
    // 獲取項目列表
    async fetchItems(params = {}) {
      this.loading = true
      this.error = null

      try {
        const response = await exampleService.getList(params)

        this.items = response.data
        this.pagination = response.pagination

        return response
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    // 獲取單一項目
    async fetchItem(id) {
      this.loading = true
      this.error = null

      try {
        const response = await exampleService.getById(id)
        this.currentItem = response.data
        return response
      } catch (error) {
        this.error = error.message
        throw error
      } finally {
        this.loading = false
      }
    },

    // 創建項目
    async createItem(data) {
      try {
        const response = await exampleService.create(data)

        // 將新項目加入列表
        this.items.unshift(response.data)
        this.pagination.total++

        return response
      } catch (error) {
        this.error = error.message
        throw error
      }
    },

    // 更新項目
    async updateItem(id, data) {
      try {
        const response = await exampleService.update(id, data)

        // 更新列表中的項目
        const index = this.items.findIndex(item => item.id === id)
        if (index !== -1) {
          this.items[index] = response.data
        }

        // 更新當前項目
        if (this.currentItem && this.currentItem.id === id) {
          this.currentItem = response.data
        }

        return response
      } catch (error) {
        this.error = error.message
        throw error
      }
    },

    // 刪除項目
    async deleteItem(id) {
      try {
        await exampleService.delete(id)

        // 從列表中移除項目
        this.items = this.items.filter(item => item.id !== id)
        this.pagination.total--

        // 清除當前項目
        if (this.currentItem && this.currentItem.id === id) {
          this.currentItem = null
        }
      } catch (error) {
        this.error = error.message
        throw error
      }
    },

    // 設定過濾器
    setFilter(key, value) {
      this.filters[key] = value
    },

    // 清除過濾器
    clearFilters() {
      this.filters = {
        search: '',
        status: '',
        category: ''
      }
    },

    // 重置狀態
    resetState() {
      this.items = []
      this.currentItem = null
      this.pagination = {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 20
      }
      this.loading = false
      this.error = null
      this.clearFilters()
    }
  }
})
```

### 📡 API 服務層

#### 服務範本
```javascript
// src/services/exampleService.js
import apiClient from '@/utils/apiClient'

class ExampleService {
  /**
   * 獲取列表
   */
  async getList(params = {}) {
    const response = await apiClient.get('/examples', { params })
    return response.data
  }

  /**
   * 獲取單一項目
   */
  async getById(id) {
    const response = await apiClient.get(`/examples/${id}`)
    return response.data
  }

  /**
   * 創建項目
   */
  async create(data) {
    const response = await apiClient.post('/examples', data)
    return response.data
  }

  /**
   * 更新項目
   */
  async update(id, data) {
    const response = await apiClient.put(`/examples/${id}`, data)
    return response.data
  }

  /**
   * 刪除項目
   */
  async delete(id) {
    const response = await apiClient.delete(`/examples/${id}`)
    return response.data
  }

  /**
   * 批次操作
   */
  async batchOperation(operation, ids) {
    const response = await apiClient.post('/examples/batch', {
      operation,
      ids
    })
    return response.data
  }

  /**
   * 匯出資料
   */
  async export(format = 'xlsx', filters = {}) {
    const params = { format, ...filters }
    const response = await apiClient.get('/examples/export', {
      params,
      responseType: 'blob'
    })

    // 創建下載連結
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `examples_${Date.now()}.${format}`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  }
}

export default new ExampleService()
```

---

## API 開發規範

### 📋 RESTful API 設計原則

#### URL 命名規範
```
GET    /api/resources           # 獲取資源列表
GET    /api/resources/{id}      # 獲取單一資源
POST   /api/resources           # 創建資源
PUT    /api/resources/{id}      # 更新資源 (完整更新)
PATCH  /api/resources/{id}      # 更新資源 (部分更新)
DELETE /api/resources/{id}      # 刪除資源
```

#### HTTP 狀態碼使用
```
200 OK                 # 請求成功
201 Created           # 資源創建成功
204 No Content        # 請求成功但無回應內容
400 Bad Request       # 請求參數錯誤
401 Unauthorized      # 未認證
403 Forbidden         # 權限不足
404 Not Found         # 資源不存在
422 Unprocessable Entity # 驗證錯誤
429 Too Many Requests # 請求過於頻繁
500 Internal Server Error # 伺服器錯誤
```

#### 回應格式標準
```json
{
  "success": true|false,
  "message": "回應訊息",
  "data": { ... } | [ ... ] | null,
  "errors": { ... } | null,
  "pagination": { ... } | null
}
```

### 🔐 API 安全規範

#### 認證頭部格式
```http
Authorization: Bearer {jwt-token}
```

#### 請求驗證
```php
// 驗證規則範例
$rules = [
    'name' => 'required|string|max:100',
    'email' => 'required|email|unique:users,email,' . $id,
    'password' => 'required|string|min:8',
    'status' => 'in:active,inactive',
    'role' => 'in:admin,user,viewer'
];
```

#### 錯誤處理
```php
// 統一錯誤回應格式
try {
    // 業務邏輯
} catch (ValidationException $e) {
    Response::validationError($e->getErrors());
} catch (AuthenticationException $e) {
    Response::unauthorized($e->getMessage());
} catch (AuthorizationException $e) {
    Response::forbidden($e->getMessage());
} catch (NotFoundException $e) {
    Response::notFound($e->getMessage());
} catch (Exception $e) {
    Response::serverError('操作失敗');
}
```

---

## 資料庫開發

### 📊 資料庫設計原則

#### 命名規範
```sql
-- 表名：複數形式，小寫，下劃線分隔
users, equipment, repair_requests

-- 欄位名：小寫，下劃線分隔
user_id, created_at, full_name

-- 索引名：表名_欄位名_idx
idx_users_email, idx_equipment_status

-- 外鍵名：fk_表名_參考表名
fk_equipment_categories, fk_repair_requests_users
```

#### 資料類型選擇
```sql
-- 主鍵
id INT PRIMARY KEY AUTO_INCREMENT

-- 字串
name VARCHAR(100)           -- 短字串
description TEXT            -- 長文本
status ENUM('active', 'inactive')  -- 固定選項

-- 數值
price DECIMAL(10, 2)        -- 金額
quantity INT                -- 整數
rate FLOAT                  -- 浮點數

-- 日期時間
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
birth_date DATE             -- 日期
start_time TIME             -- 時間

-- JSON
specifications JSON         -- 結構化資料
```

### 🔄 資料庫遷移

#### 遷移腳本範本
```sql
-- migration_001_create_examples_table.sql
CREATE TABLE examples (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    metadata JSON,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_examples_status (status),
    INDEX idx_examples_created_at (created_at),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入初始資料
INSERT INTO examples (name, email, description, created_by) VALUES
('範例項目', 'example@test.com', '這是一個範例項目', 1);
```

#### 索引優化
```sql
-- 單欄位索引
CREATE INDEX idx_equipment_status ON equipment(status);

-- 複合索引
CREATE INDEX idx_repair_requests_status_priority ON repair_requests(status, priority);

-- 唯一索引
CREATE UNIQUE INDEX uk_users_email ON users(email);

-- 全文搜索索引
CREATE FULLTEXT INDEX ft_announcements_content ON security_announcements(title, content);
```

### 📈 效能優化

#### 查詢優化
```sql
-- 使用 EXPLAIN 分析查詢
EXPLAIN SELECT * FROM equipment
WHERE status = 'active'
AND department = 'IT'
ORDER BY created_at DESC
LIMIT 20;

-- 避免 SELECT *
SELECT id, name, status FROM equipment WHERE status = 'active';

-- 使用適當的 JOIN
SELECT e.name, c.name as category_name
FROM equipment e
LEFT JOIN equipment_categories c ON e.category_id = c.id;

-- 分頁查詢優化
SELECT * FROM equipment
WHERE id > 1000
ORDER BY id
LIMIT 20;
```

---

## 測試指南

### 🧪 後端測試

#### 單元測試範本
```php
<?php
// tests/Controllers/ExampleControllerTest.php

use PHPUnit\Framework\TestCase;
use App\Controllers\ExampleController;
use App\Core\Database;

class ExampleControllerTest extends TestCase
{
    private $controller;
    private $db;

    protected function setUp(): void
    {
        $this->controller = new ExampleController();
        $this->db = $this->createMock(Database::class);
    }

    public function testIndex()
    {
        // 模擬資料庫回應
        $mockData = [
            'data' => [
                ['id' => 1, 'name' => 'Test Item'],
                ['id' => 2, 'name' => 'Test Item 2']
            ],
            'pagination' => [
                'current_page' => 1,
                'total' => 2
            ]
        ];

        $this->db->expects($this->once())
                 ->method('paginate')
                 ->willReturn($mockData);

        // 執行測試
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertCount(2, $response['data']);
    }

    public function testStore()
    {
        // 設定請求資料
        $_POST = [
            'name' => 'New Item',
            'email' => 'test@example.com'
        ];

        $this->db->expects($this->once())
                 ->method('insert')
                 ->willReturn(1);

        // 執行測試
        ob_start();
        $this->controller->store();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('創建成功', $response['message']);
    }
}
```

#### API 測試腳本
```bash
#!/bin/bash
# test-api.sh - API 測試腳本

API_BASE="http://localhost:9000/api"
TOKEN=""

# 登入並獲取 Token
login() {
    response=$(curl -s -X POST "$API_BASE/auth/login" \
        -H "Content-Type: application/json" \
        -d '{"username":"admin","password":"password"}')

    TOKEN=$(echo $response | jq -r '.data.token')
    echo "Token: $TOKEN"
}

# 測試設備列表
test_equipment_list() {
    echo "Testing equipment list..."
    curl -s -X GET "$API_BASE/equipment" \
        -H "Authorization: Bearer $TOKEN" | jq '.'
}

# 測試創建設備
test_create_equipment() {
    echo "Testing create equipment..."
    curl -s -X POST "$API_BASE/equipment" \
        -H "Authorization: Bearer $TOKEN" \
        -H "Content-Type: application/json" \
        -d '{
            "name": "Test Equipment",
            "ip_address": "192.168.1.100",
            "status": "active"
        }' | jq '.'
}

# 執行測試
login
test_equipment_list
test_create_equipment
```

### 🎨 前端測試

#### 組件測試範本
```javascript
// tests/components/Button.test.js
import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import Button from '@/components/ui/Button.vue'

describe('Button Component', () => {
  it('renders properly', () => {
    const wrapper = mount(Button, {
      props: { variant: 'primary' },
      slots: { default: 'Click me' }
    })

    expect(wrapper.text()).toContain('Click me')
    expect(wrapper.classes()).toContain('btn-primary')
  })

  it('emits click event', async () => {
    const wrapper = mount(Button)

    await wrapper.trigger('click')

    expect(wrapper.emitted()).toHaveProperty('click')
  })

  it('handles loading state', () => {
    const wrapper = mount(Button, {
      props: { loading: true }
    })

    expect(wrapper.find('.loading-spinner').exists()).toBe(true)
    expect(wrapper.attributes('disabled')).toBeDefined()
  })
})
```

#### E2E 測試範本
```javascript
// tests/e2e/login.test.js
import { test, expect } from '@playwright/test'

test.describe('Login Flow', () => {
  test('successful login', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[name="username"]', 'admin')
    await page.fill('input[name="password"]', 'password')
    await page.click('button[type="submit"]')

    await expect(page).toHaveURL('/dashboard')
    await expect(page.locator('.welcome-message')).toContainText('歡迎')
  })

  test('failed login', async ({ page }) => {
    await page.goto('/login')

    await page.fill('input[name="username"]', 'admin')
    await page.fill('input[name="password"]', 'wrongpassword')
    await page.click('button[type="submit"]')

    await expect(page.locator('.error-message')).toContainText('用戶名或密碼錯誤')
  })
})
```

---

## 代碼規範

### 📝 PHP 代碼規範

#### PSR-12 標準
```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class ExampleController
{
    private Database $db;
    private const MAX_ITEMS = 100;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        $request = new Request();
        $page = (int) $request->input('page', 1);

        if ($page < 1) {
            Response::error('Invalid page number');
            return;
        }

        $items = $this->db->fetchAll(
            'SELECT * FROM items WHERE status = ? ORDER BY created_at DESC',
            ['active']
        );

        Response::success($items);
    }
}
```

#### 命名規範
```php
// 類名：PascalCase
class EquipmentController {}

// 方法名：camelCase
public function updateStatus() {}

// 變數名：camelCase
$equipmentList = [];

// 常數：SCREAMING_SNAKE_CASE
const MAX_FILE_SIZE = 5242880;

// 檔案名：PascalCase.php
EquipmentController.php
```

### 🎨 JavaScript/Vue 代碼規範

#### ESLint 配置
```javascript
// .eslintrc.js
module.exports = {
  extends: [
    '@vue/eslint-config-prettier',
    'plugin:vue/vue3-recommended'
  ],
  rules: {
    'vue/component-name-in-template-casing': ['error', 'PascalCase'],
    'vue/component-definition-name-casing': ['error', 'PascalCase'],
    'vue/prop-name-casing': ['error', 'camelCase'],
    'prettier/prettier': ['error', {
      semi: false,
      singleQuote: true,
      trailingComma: 'es5'
    }]
  }
}
```

#### Vue 組件規範
```vue
<template>
  <!-- 使用 kebab-case 屬性 -->
  <BaseButton
    :is-loading="loading"
    :variant="buttonVariant"
    @click="handleClick"
  >
    {{ buttonText }}
  </BaseButton>
</template>

<script setup>
// 使用 camelCase 變數名
const isLoading = ref(false)
const buttonVariant = ref('primary')

// 使用 PascalCase 組件名
import BaseButton from '@/components/ui/BaseButton.vue'

// Props 定義
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  }
})

// Emits 定義
const emit = defineEmits(['update:modelValue', 'submit'])
</script>
```

### 💄 CSS/SCSS 規範

#### Tailwind CSS 使用規範
```vue
<template>
  <!-- 按邏輯分組 CSS 類 -->
  <div class="
    <!-- Layout -->
    flex items-center justify-between
    <!-- Spacing -->
    px-4 py-2 mb-4
    <!-- Appearance -->
    bg-white border border-gray-200 rounded-lg shadow-sm
    <!-- States -->
    hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500
    <!-- Responsive -->
    md:px-6 lg:py-3
  ">
    <!-- 內容 -->
  </div>
</template>

<style scoped>
/* 自定義樣式使用 CSS 變數 */
.custom-button {
  background-color: var(--color-primary);
  border-radius: var(--border-radius);
  transition: all var(--transition-duration) ease;
}

/* 使用 @apply 指令 */
.card {
  @apply bg-white rounded-lg shadow-sm border border-gray-200;
}
</style>
```

---

## Git 工作流程

### 🌿 分支策略

#### Git Flow 分支模型
```
main                 # 生產環境分支
├── develop          # 開發分支
│   ├── feature/equipment-management    # 功能分支
│   ├── feature/repair-system          # 功能分支
│   └── feature/announcement-module    # 功能分支
├── release/v1.0.0   # 發布分支
└── hotfix/fix-login # 熱修復分支
```

#### 分支命名規範
```bash
# 功能分支
feature/equipment-management
feature/user-authentication
feature/file-upload

# 修復分支
bugfix/fix-login-error
bugfix/repair-status-update

# 熱修復分支
hotfix/security-patch
hotfix/critical-bug-fix

# 發布分支
release/v1.0.0
release/v1.1.0
```

### 📝 提交訊息規範

#### Conventional Commits
```bash
# 格式
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]

# 範例
feat(equipment): add image upload functionality
fix(auth): resolve JWT token expiration issue
docs(api): update authentication endpoint documentation
style(ui): improve button hover effects
refactor(database): optimize query performance
test(equipment): add unit tests for CRUD operations
chore(deps): update dependencies to latest versions
```

#### 提交類型
```
feat:     新功能
fix:      修復 bug
docs:     文檔更新
style:    代碼格式調整
refactor: 重構代碼
test:     測試相關
chore:    構建過程或輔助工具變動
perf:     性能優化
ci:       CI 配置文件和腳本變動
revert:   回滾之前的提交
```

### 🔄 工作流程

#### 開發新功能
```bash
# 1. 切換到 develop 分支並更新
git checkout develop
git pull origin develop

# 2. 創建功能分支
git checkout -b feature/new-feature

# 3. 開發並提交
git add .
git commit -m "feat(module): add new feature"

# 4. 推送分支
git push origin feature/new-feature

# 5. 創建 Pull Request
# 在 GitHub/GitLab 創建 PR，目標分支為 develop

# 6. 代碼審查並合併
# 審查通過後合併到 develop

# 7. 清理分支
git checkout develop
git pull origin develop
git branch -d feature/new-feature
```

#### 發布流程
```bash
# 1. 從 develop 創建 release 分支
git checkout develop
git pull origin develop
git checkout -b release/v1.0.0

# 2. 更新版本號和文檔
# 修改 package.json, composer.json 等版本號

# 3. 測試和修復
git commit -m "chore(release): prepare v1.0.0"

# 4. 合併到 main 和 develop
git checkout main
git merge release/v1.0.0
git tag v1.0.0
git push origin main --tags

git checkout develop
git merge release/v1.0.0
git push origin develop

# 5. 清理分支
git branch -d release/v1.0.0
```

---

## 除錯與優化

### 🐛 除錯技巧

#### 後端除錯
```php
// 1. 錯誤日誌
error_log('Debug: ' . print_r($data, true));

// 2. 變數轉儲
var_dump($variable);
die(); // 停止執行

// 3. 追蹤執行
debug_backtrace();

// 4. 效能分析
$start = microtime(true);
// 執行代碼
$end = microtime(true);
error_log('Execution time: ' . ($end - $start) . ' seconds');

// 5. SQL 查詢除錯
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
error_log('SQL: ' . $stmt->queryString);
error_log('Params: ' . print_r($params, true));
```

#### 前端除錯
```javascript
// 1. Console 除錯
console.log('Debug:', data)
console.table(arrayData)
console.time('operation')
// 執行代碼
console.timeEnd('operation')

// 2. Vue DevTools
// 安裝瀏覽器擴展進行除錯

// 3. 網路請求除錯
axios.interceptors.request.use(request => {
  console.log('Request:', request)
  return request
})

axios.interceptors.response.use(
  response => {
    console.log('Response:', response)
    return response
  },
  error => {
    console.error('Error:', error)
    return Promise.reject(error)
  }
)

// 4. 效能監控
performance.mark('start')
// 執行代碼
performance.mark('end')
performance.measure('operation', 'start', 'end')
console.log(performance.getEntriesByType('measure'))
```

### ⚡ 效能優化

#### 後端優化
```php
// 1. 資料庫查詢優化
// 使用索引
CREATE INDEX idx_equipment_status ON equipment(status);

// 避免 N+1 查詢
$equipment = $db->fetchAll("
    SELECT e.*, c.name as category_name
    FROM equipment e
    LEFT JOIN equipment_categories c ON e.category_id = c.id
");

// 2. 快取使用
$cacheKey = "equipment_list_{$page}_{$perPage}";
$data = $cache->get($cacheKey);

if (!$data) {
    $data = $db->fetchAll($sql, $params);
    $cache->set($cacheKey, $data, 3600); // 快取 1 小時
}

// 3. 分頁優化
// 使用 LIMIT OFFSET 替代大偏移量
SELECT * FROM equipment WHERE id > ? ORDER BY id LIMIT ?
```

#### 前端優化
```javascript
// 1. 組件懶載入
const Equipment = () => import('@/views/Equipment.vue')

// 2. 圖片懶載入
<img
  v-lazy="imageUrl"
  alt="設備圖片"
  class="w-full h-32 object-cover"
/>

// 3. 虛擬滾動 (大量數據)
import VirtualList from '@tanstack/vue-virtual'

// 4. 防抖和節流
import { debounce } from 'lodash-es'

const searchHandler = debounce((query) => {
  // 搜尋邏輯
}, 300)

// 5. Memoization
const expensiveComputation = computed(() => {
  return heavyCalculation(props.data)
})
```

---

## 📚 參考資源

### 官方文檔
- [PHP Manual](https://www.php.net/manual/en/)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Pinia Documentation](https://pinia.vuejs.org/)

### 開發工具
- [PHPStorm](https://www.jetbrains.com/phpstorm/) - PHP IDE
- [VS Code](https://code.visualstudio.com/) - 編輯器
- [Postman](https://www.postman.com/) - API 測試
- [TablePlus](https://tableplus.com/) - 資料庫管理

### 測試工具
- [PHPUnit](https://phpunit.de/) - PHP 單元測試
- [Vitest](https://vitest.dev/) - Vue 測試框架
- [Playwright](https://playwright.dev/) - E2E 測試

### 部署工具
- [Docker](https://www.docker.com/) - 容器化
- [GitHub Actions](https://github.com/features/actions) - CI/CD
- [Vercel](https://vercel.com/) - 前端部署

---

**📝 注意事項：**
1. 遵循代碼規範和最佳實踐
2. 寫清楚的提交訊息和文檔
3. 進行充分的測試
4. 考慮安全性和效能
5. 定期更新依賴套件

**🎯 開發目標：**
- 編寫乾淨、可維護的代碼
- 確保系統安全性和穩定性
- 提供良好的用戶體驗
- 建立完善的測試覆蓋
- 保持代碼文檔的更新
