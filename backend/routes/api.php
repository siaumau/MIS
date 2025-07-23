<?php

use App\Core\Router;

$router = new Router();

// ===========================
// 公開路由（無需認證）
// ===========================

// 認證相關
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/refresh', 'AuthController@refresh');

// 系統資訊
$router->get('/system/info', function() {
    $config = include __DIR__ . '/../config/app.php';
    \App\Core\Response::success([
        'name' => $config['name'],
        'version' => $config['version'],
        'timezone' => $config['timezone']
    ]);
});

// ===========================
// 需要認證的路由
// ===========================

$router->group(['middleware' => 'AuthMiddleware'], function($router) {

    // 認證相關
    $router->post('/auth/logout', 'AuthController@logout');
    $router->get('/auth/me', 'AuthController@me');
    $router->post('/auth/change-password', 'AuthController@changePassword');
    $router->put('/auth/profile', 'AuthController@updateProfile');

    // 圖片上傳
    $router->post('/upload/image', 'UploadController@image');
    $router->delete('/upload/image/{id}', 'UploadController@deleteImage');

    // ===========================
    // 設備管理
    // ===========================
    
    // 設備 CRUD
    $router->get('/equipment', 'EquipmentController@index');
    $router->get('/equipment/{id}', 'EquipmentController@show');
    $router->post('/equipment', 'EquipmentController@store');
    $router->put('/equipment/{id}', 'EquipmentController@update');
    $router->delete('/equipment/{id}', 'EquipmentController@destroy');
    
    // 設備分類
    $router->get('/categories', 'CategoryController@index');
    $router->get('/categories/{id}', 'CategoryController@show');
    $router->post('/categories', 'CategoryController@store');
    $router->put('/categories/{id}', 'CategoryController@update');
    $router->delete('/categories/{id}', 'CategoryController@destroy');
    
    // 設備匯入匯出
    $router->post('/equipment/import', 'EquipmentController@import');
    $router->get('/equipment/export', 'EquipmentController@export');

    // ===========================
    // 報修管理
    // ===========================
    
    // 報修單 CRUD
    $router->get('/repairs', 'RepairController@index');
    $router->get('/repairs/{id}', 'RepairController@show');
    $router->post('/repairs', 'RepairController@store');
    $router->put('/repairs/{id}/status', 'RepairController@updateStatus');
    $router->post('/repairs/{id}/comment', 'RepairController@addComment');
    
    // 報修統計
    $router->get('/repairs/statistics', 'RepairController@statistics');

    // ===========================
    // 資訊安全佈達
    // ===========================
    
    // 公告相關
    $router->get('/announcements', 'AnnouncementController@index');
    $router->get('/announcements/{id}', 'AnnouncementController@show');
    $router->post('/announcements/{id}/acknowledge', 'AnnouncementController@acknowledge');
    
    // 我的公告
    $router->get('/my/announcements', 'AnnouncementController@myAnnouncements');

    // ===========================
    // 網路拓樸管理
    // ===========================
    
    $router->get('/topologies', 'TopologyController@index');
    $router->get('/topologies/{id}', 'TopologyController@show');
    $router->post('/topologies', 'TopologyController@store');
    $router->put('/topologies/{id}', 'TopologyController@update');
    $router->delete('/topologies/{id}', 'TopologyController@destroy');
    
    // 拓樸節點
    $router->get('/topologies/{id}/nodes', 'TopologyController@getNodes');
    $router->post('/topologies/{id}/nodes', 'TopologyController@addNode');
    $router->put('/topologies/{id}/nodes/{nodeId}', 'TopologyController@updateNode');
    $router->delete('/topologies/{id}/nodes/{nodeId}', 'TopologyController@deleteNode');

    // ===========================
    // VM 伺服器管理
    // ===========================
    
    $router->get('/vm-servers', 'VmServerController@index');
    $router->get('/vm-servers/{id}', 'VmServerController@show');
    $router->post('/vm-servers', 'VmServerController@store');
    $router->put('/vm-servers/{id}', 'VmServerController@update');
    $router->delete('/vm-servers/{id}', 'VmServerController@destroy');
    
    // VM 伺服器狀態監控
    $router->get('/vm-servers/{id}/status', 'VmServerController@getStatus');
    $router->post('/vm-servers/{id}/action', 'VmServerController@performAction');

    // ===========================
    // 系統帳號管理
    // ===========================
    
    $router->get('/system-accounts', 'SystemAccountController@index');
    $router->get('/system-accounts/{id}', 'SystemAccountController@show');
    $router->post('/system-accounts', 'SystemAccountController@store');
    $router->put('/system-accounts/{id}', 'SystemAccountController@update');
    $router->delete('/system-accounts/{id}', 'SystemAccountController@destroy');

    // ===========================
    // 定期付費管理
    // ===========================
    
    $router->get('/payments', 'PaymentController@index');
    $router->get('/payments/{id}', 'PaymentController@show');
    $router->post('/payments', 'PaymentController@store');
    $router->put('/payments/{id}', 'PaymentController@update');
    $router->delete('/payments/{id}', 'PaymentController@destroy');
    
    // 付費記錄
    $router->get('/payments/{id}/records', 'PaymentController@getRecords');
    $router->post('/payments/{id}/records', 'PaymentController@addRecord');
    
    // 即將到期提醒
    $router->get('/payments/upcoming', 'PaymentController@getUpcoming');

    // ===========================
    // 追蹤碼管理
    // ===========================
    
    $router->get('/tracking-codes', 'TrackingCodeController@index');
    $router->get('/tracking-codes/{id}', 'TrackingCodeController@show');
    $router->post('/tracking-codes', 'TrackingCodeController@store');
    $router->put('/tracking-codes/{id}', 'TrackingCodeController@update');
    $router->delete('/tracking-codes/{id}', 'TrackingCodeController@destroy');
    
    // 部署狀態檢查
    $router->post('/tracking-codes/{id}/check', 'TrackingCodeController@checkDeployment');

    // ===========================
    // 系統日誌與統計
    // ===========================
    
    $router->get('/logs', 'LogController@index');
    $router->get('/logs/{id}', 'LogController@show');
    
    // 系統統計儀表板
    $router->get('/dashboard/stats', 'DashboardController@getStatistics');
    $router->get('/dashboard/charts', 'DashboardController@getChartData');

});

// ===========================
// 管理員專用路由
// ===========================

$router->group(['middleware' => ['AuthMiddleware', 'AdminMiddleware']], function($router) {

    // 用戶管理
    $router->get('/admin/users', 'UserController@index');
    $router->get('/admin/users/{id}', 'UserController@show');
    $router->post('/admin/users', 'UserController@store');
    $router->put('/admin/users/{id}', 'UserController@update');
    $router->delete('/admin/users/{id}', 'UserController@destroy');
    $router->put('/admin/users/{id}/status', 'UserController@updateStatus');
    
    // 報修單指派
    $router->post('/admin/repairs/{id}/assign', 'RepairController@assign');
    
    // 公告管理
    $router->post('/admin/announcements', 'AnnouncementController@store');
    $router->put('/admin/announcements/{id}', 'AnnouncementController@update');
    $router->delete('/admin/announcements/{id}', 'AnnouncementController@destroy');
    $router->post('/admin/announcements/{id}/send', 'AnnouncementController@send');
    $router->get('/admin/announcements/{id}/statistics', 'AnnouncementController@readStatistics');
    
    // 設備分類管理
    $router->post('/admin/equipment-categories', 'EquipmentCategoryController@store');
    $router->put('/admin/equipment-categories/{id}', 'EquipmentCategoryController@update');
    $router->delete('/admin/equipment-categories/{id}', 'EquipmentCategoryController@destroy');
    
    // 系統設定
    $router->get('/admin/settings', 'SettingController@index');
    $router->put('/admin/settings', 'SettingController@update');
    
    // 系統備份
    $router->post('/admin/backup', 'BackupController@create');
    $router->get('/admin/backups', 'BackupController@index');
    $router->post('/admin/backups/{id}/restore', 'BackupController@restore');
    $router->delete('/admin/backups/{id}', 'BackupController@destroy');

});

// ===========================
// 靜態檔案服務
// ===========================

// 圖片檔案
$router->get('/files/images/{filename}', function($params) {
    $filename = $params['filename'];
    $filepath = "uploads/images/{$filename}";
    
    if (file_exists($filepath)) {
        \App\Core\Response::file($filepath);
    } else {
        \App\Core\Response::notFound('檔案不存在');
    }
});

// 縮圖檔案
$router->get('/files/thumbnails/{filename}', function($params) {
    $filename = $params['filename'];
    $filepath = "uploads/images/thumbnails/{$filename}";
    
    if (file_exists($filepath)) {
        \App\Core\Response::file($filepath);
    } else {
        \App\Core\Response::notFound('檔案不存在');
    }
});

// 附件下載
$router->get('/files/attachments/{filename}', function($params) {
    $filename = $params['filename'];
    $filepath = "uploads/announcements/{$filename}";
    
    if (file_exists($filepath)) {
        \App\Core\Response::download($filepath);
    } else {
        \App\Core\Response::notFound('檔案不存在');
    }
});

return $router;