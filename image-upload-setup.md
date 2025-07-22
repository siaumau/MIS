# 圖片貼上上傳功能設置說明

## 功能特色

- 🖼️ 支援拖拽上傳圖片
- 📋 支援剪貼板貼上圖片 (Ctrl+V)
- 👆 支援點擊選擇檔案
- 🖥️ 即時圖片預覽
- 📊 檔案大小和格式驗證
- 🗑️ 可刪除已選圖片
- 📈 上傳進度指示
- 🔄 自動生成縮圖
- 💾 支援多種圖片格式 (JPG, PNG, GIF, WebP)

## 安裝與設置

### 1. 後端設置 (PHP)

#### 創建必要目錄
```bash
mkdir -p uploads/images/thumbnails
mkdir -p logs
chmod 755 uploads/images
chmod 755 uploads/images/thumbnails
chmod 755 logs
```

#### 確保 PHP 擴展
確保您的 PHP 環境已啟用以下擴展：
```php
extension=gd
extension=fileinfo
```

#### 配置 PHP 設定
在 `php.ini` 中設置：
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
memory_limit = 128M
```

### 2. 前端設置 (Vue 3)

#### 安裝依賴
```bash
npm install vue@next
npm install -D tailwindcss postcss autoprefixer
```

#### Tailwind CSS 設置
```bash
npx tailwindcss init -p
```

在 `tailwind.config.js` 中：
```javascript
module.exports = {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

### 3. 使用方式

#### 基本使用
```vue
<template>
  <ImagePasteUpload
    :api-endpoint="'/api/upload-image.php'"
    :max-file-size="5 * 1024 * 1024"
    :max-files="10"
    @upload-success="handleUploadSuccess"
    @upload-error="handleUploadError"
  />
</template>

<script setup>
import ImagePasteUpload from './ImagePasteUpload.vue'

const handleUploadSuccess = (results) => {
  console.log('上傳成功:', results)
}

const handleUploadError = (error) => {
  console.error('上傳失敗:', error)
}
</script>
```

#### 組件參數

| 參數 | 類型 | 預設值 | 說明 |
|------|------|--------|------|
| `max-file-size` | Number | 5MB | 單個檔案最大大小 |
| `max-files` | Number | 10 | 最大檔案數量 |
| `api-endpoint` | String | '/api/upload-image.php' | 後端 API 端點 |

#### 事件

| 事件名 | 參數 | 說明 |
|--------|------|------|
| `upload-success` | results | 上傳成功時觸發 |
| `upload-error` | error | 上傳失敗時觸發 |

### 4. API 回應格式

#### 成功回應
```json
{
  "success": true,
  "message": "圖片上傳成功",
  "data": {
    "filename": "image_20231201123000_abc12345.jpg",
    "original_name": "photo.jpg",
    "file_path": "uploads/images/image_20231201123000_abc12345.jpg",
    "thumbnail_path": "uploads/images/thumbnails/thumb_image_20231201123000_abc12345.jpg",
    "file_size": 1024000,
    "mime_type": "image/jpeg",
    "dimensions": {
      "width": 1920,
      "height": 1080
    },
    "upload_time": "2023-12-01 12:30:00"
  }
}
```

#### 錯誤回應
```json
{
  "success": false,
  "message": "檔案大小超過限制：5 MB"
}
```

### 5. 安全性建議

#### 檔案驗證
- 驗證檔案 MIME 類型
- 檢查檔案大小限制
- 使用 `getimagesize()` 驗證圖片完整性
- 生成隨機檔名防止路徑攻擊

#### 目錄權限
```bash
# 設置適當權限
chmod 755 uploads/
chmod 644 uploads/images/*
```

#### 防止直接訪問
在 `uploads/.htaccess` 中：
```apache
# 防止 PHP 檔案執行
<Files "*.php">
    Order Allow,Deny
    Deny from all
</Files>

# 只允許圖片檔案
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>
```

### 6. 故障排除

#### 常見問題

**問題**: 上傳失敗，顯示 "無法創建上傳目錄"
**解決**: 檢查目錄權限，確保 web 服務器可以寫入

**問題**: 圖片過大被拒絕
**解決**: 檢查 PHP 配置中的 `upload_max_filesize` 和 `post_max_size`

**問題**: 縮圖生成失敗
**解決**: 確保 PHP GD 擴展已啟用

**問題**: CORS 錯誤
**解決**: 檢查 API 檔案中的 CORS 標頭設置

#### 日誌檢查
```bash
# 檢查 PHP 錯誤日誌
tail -f /var/log/php_errors.log

# 檢查上傳日誌
tail -f logs/upload.log
```

### 7. 效能優化

#### 圖片壓縮
可以在後端添加圖片壓縮邏輯：
```php
// 壓縮 JPEG 圖片
imagejpeg($image, $path, 85); // 85% 品質

// 壓縮 PNG 圖片
imagepng($image, $path, 6); // 壓縮等級 0-9
```

#### 快取設置
在 web 服務器中設置圖片快取：
```apache
# Apache
<LocationMatch "\.(jpg|jpeg|png|gif|webp)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</LocationMatch>
```

### 8. 擴展功能

#### 圖片裁切
可以整合圖片裁切功能：
```javascript
// 使用 canvas 進行圖片裁切
const canvas = document.createElement('canvas');
const ctx = canvas.getContext('2d');
// ... 裁切邏輯
```

#### 批次處理
支援批次上傳和處理：
```php
// 批次處理多個圖片
foreach ($_FILES as $file) {
    // 處理每個檔案
}
```