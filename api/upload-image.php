<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只允許 POST 請求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => '只允許 POST 請求'
    ]);
    exit();
}

// 配置設定
$config = [
    'upload_dir' => '../uploads/images/',
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
];

// 確保上傳目錄存在
if (!file_exists($config['upload_dir'])) {
    if (!mkdir($config['upload_dir'], 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '無法創建上傳目錄'
        ]);
        exit();
    }
}

try {
    // 檢查必要參數
    if (!isset($_POST['image']) || !isset($_POST['filename'])) {
        throw new Exception('缺少必要參數');
    }

    $base64_image = $_POST['image'];
    $original_filename = $_POST['filename'];

    // 驗證 base64 格式
    if (!preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) {
        throw new Exception('無效的圖片格式');
    }

    // 獲取圖片類型
    $image_type = strtolower($type[1]);
    $mime_type = 'image/' . $image_type;

    // 檢查允許的圖片類型
    if (!in_array($mime_type, $config['allowed_types'])) {
        throw new Exception('不支援的圖片格式：' . $image_type);
    }

    // 移除 base64 前綴
    $base64_image = preg_replace('/^data:image\/\w+;base64,/', '', $base64_image);
    
    // 解碼 base64
    $image_data = base64_decode($base64_image);
    
    if ($image_data === false) {
        throw new Exception('base64 解碼失敗');
    }

    // 檢查檔案大小
    if (strlen($image_data) > $config['max_file_size']) {
        throw new Exception('檔案大小超過限制：' . formatFileSize($config['max_file_size']));
    }

    // 生成唯一檔名
    $file_extension = $image_type === 'jpeg' ? 'jpg' : $image_type;
    $filename = generateUniqueFilename($original_filename, $file_extension);
    $file_path = $config['upload_dir'] . $filename;

    // 保存圖片
    if (file_put_contents($file_path, $image_data) === false) {
        throw new Exception('保存圖片失敗');
    }

    // 驗證圖片完整性
    $image_info = getimagesize($file_path);
    if ($image_info === false) {
        unlink($file_path); // 刪除無效檔案
        throw new Exception('無效的圖片檔案');
    }

    // 生成縮圖（可選）
    $thumbnail_path = generateThumbnail($file_path, $filename, $image_info);

    // 返回成功結果
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => '圖片上傳成功',
        'data' => [
            'filename' => $filename,
            'original_name' => $original_filename,
            'file_path' => 'uploads/images/' . $filename,
            'thumbnail_path' => $thumbnail_path ? 'uploads/images/thumbnails/' . basename($thumbnail_path) : null,
            'file_size' => strlen($image_data),
            'mime_type' => $mime_type,
            'dimensions' => [
                'width' => $image_info[0],
                'height' => $image_info[1]
            ],
            'upload_time' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * 生成唯一檔名
 */
function generateUniqueFilename($original_filename, $extension) {
    $name = pathinfo($original_filename, PATHINFO_FILENAME);
    $safe_name = preg_replace('/[^a-zA-Z0-9\-_]/', '', $name);
    $safe_name = $safe_name ?: 'image';
    
    $timestamp = date('YmdHis');
    $random = substr(md5(uniqid()), 0, 8);
    
    return $safe_name . '_' . $timestamp . '_' . $random . '.' . $extension;
}

/**
 * 生成縮圖
 */
function generateThumbnail($source_path, $filename, $image_info) {
    $thumbnail_dir = '../uploads/images/thumbnails/';
    
    // 確保縮圖目錄存在
    if (!file_exists($thumbnail_dir)) {
        if (!mkdir($thumbnail_dir, 0755, true)) {
            return false;
        }
    }

    $thumbnail_filename = 'thumb_' . $filename;
    $thumbnail_path = $thumbnail_dir . $thumbnail_filename;
    
    $max_width = 300;
    $max_height = 300;
    
    list($width, $height, $type) = $image_info;
    
    // 計算新尺寸
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);
    
    // 創建圖片資源
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source) {
        return false;
    }
    
    // 創建縮圖
    $thumbnail = imagecreatetruecolor($new_width, $new_height);
    
    // 處理透明度（PNG/GIF）
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
    }
    
    // 縮放圖片
    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // 保存縮圖
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($thumbnail, $thumbnail_path, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($thumbnail, $thumbnail_path);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($thumbnail, $thumbnail_path);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($thumbnail, $thumbnail_path, 85);
            break;
    }
    
    // 清理記憶體
    imagedestroy($source);
    imagedestroy($thumbnail);
    
    return $success ? $thumbnail_path : false;
}

/**
 * 格式化檔案大小
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * 記錄上傳日誌
 */
function logUpload($filename, $original_name, $file_size, $ip_address) {
    $log_file = '../logs/upload.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip' => $ip_address,
        'filename' => $filename,
        'original_name' => $original_name,
        'file_size' => $file_size
    ];
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}
?>