<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

/**
 * 設備管理控制器
 */
class EquipmentController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取設備列表
     */
    public function index(): void
    {
        $request = new Request();
        $page = (int)$request->input('page', 1);
        $perPage = min((int)$request->input('per_page', 20), 100);
        $search = $request->input('search', '');
        $categoryId = $request->input('category_id');
        $status = $request->input('status');
        $department = $request->input('department');

        // 構建查詢條件
        $conditions = [];
        $params = [];

        if ($search) {
            $conditions[] = "(e.name LIKE ? OR e.ip_address LIKE ? OR e.property_number LIKE ? OR e.serial_number LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        if ($categoryId) {
            $conditions[] = "e.category_id = ?";
            $params[] = $categoryId;
        }

        if ($status) {
            $conditions[] = "e.status = ?";
            $params[] = $status;
        }

        if ($department) {
            $conditions[] = "e.department = ?";
            $params[] = $department;
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "
            SELECT 
                e.*,
                c.name as category_name,
                u.full_name as created_by_name,
                (SELECT COUNT(*) FROM equipment_images ei WHERE ei.equipment_id = e.id) as image_count
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.id
            LEFT JOIN users u ON e.created_by = u.id
            {$whereClause}
            ORDER BY e.created_at DESC
        ";

        $result = $this->db->paginate($sql, $params, $page, $perPage);
        Response::paginated($result);
    }

    /**
     * 獲取單一設備詳細資訊
     */
    public function show(array $params): void
    {
        $id = (int)$params['id'];

        $equipment = $this->db->fetch("
            SELECT 
                e.*,
                c.name as category_name,
                u.full_name as created_by_name
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ", [$id]);

        if (!$equipment) {
            Response::notFound('設備不存在');
        }

        // 獲取設備圖片
        $images = $this->db->fetchAll("
            SELECT id, filename, original_name, file_path, thumbnail_path, file_size, is_primary
            FROM equipment_images 
            WHERE equipment_id = ? 
            ORDER BY is_primary DESC, created_at ASC
        ", [$id]);

        $equipment['images'] = $images;

        // 解析 specifications JSON
        if ($equipment['specifications']) {
            $equipment['specifications'] = json_decode($equipment['specifications'], true);
        }

        Response::success($equipment);
    }

    /**
     * 創建新設備
     */
    public function store(): void
    {
        $request = new Request();
        
        // 驗證輸入
        $errors = $request->validate([
            'name' => 'required|max:100',
            'category_id' => 'integer',
            'brand' => 'max:50',
            'model' => 'max:100',
            'status' => 'in:active,inactive,maintenance,retired'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only([
            'name', 'category_id', 'brand', 'model', 'serial_number', 'property_number',
            'ip_address', 'mac_address', 'location', 'department', 'responsible_person',
            'purchase_date', 'warranty_end_date', 'price', 'vendor', 'status', 'description'
        ]);

        // 處理規格資訊
        $specifications = $request->input('specifications');
        if ($specifications && is_array($specifications)) {
            $data['specifications'] = json_encode($specifications);
        }

        // 檢查 IP 位址是否重複
        if (!empty($data['ip_address'])) {
            $existingEquipment = $this->db->fetch(
                "SELECT id FROM equipment WHERE ip_address = ?",
                [$data['ip_address']]
            );

            if ($existingEquipment) {
                Response::error('該 IP 位址已被其他設備使用');
            }
        }

        // 檢查財產編號是否重複
        if (!empty($data['property_number'])) {
            $existingEquipment = $this->db->fetch(
                "SELECT id FROM equipment WHERE property_number = ?",
                [$data['property_number']]
            );

            if ($existingEquipment) {
                Response::error('該財產編號已被其他設備使用');
            }
        }

        $data['created_by'] = $GLOBALS['current_user']['id'];

        try {
            $this->db->beginTransaction();

            $equipmentId = $this->db->insert('equipment', $data);

            // 處理圖片上傳
            $imageIds = $request->input('image_ids', []);
            if (!empty($imageIds)) {
                $this->attachImages($equipmentId, $imageIds);
            }

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('create', 'equipment', $equipmentId, null, $data);

            // 獲取完整的設備資訊
            $equipment = $this->db->fetch("
                SELECT e.*, c.name as category_name
                FROM equipment e
                LEFT JOIN equipment_categories c ON e.category_id = c.id
                WHERE e.id = ?
            ", [$equipmentId]);

            Response::created($equipment, '設備創建成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('設備創建失敗：' . $e->getMessage());
        }
    }

    /**
     * 更新設備
     */
    public function update(array $params): void
    {
        $id = (int)$params['id'];
        $request = new Request();

        // 檢查設備是否存在
        $equipment = $this->db->fetch("SELECT * FROM equipment WHERE id = ?", [$id]);
        if (!$equipment) {
            Response::notFound('設備不存在');
        }

        // 驗證輸入
        $errors = $request->validate([
            'name' => 'required|max:100',
            'category_id' => 'integer',
            'brand' => 'max:50',
            'model' => 'max:100',
            'status' => 'in:active,inactive,maintenance,retired'
        ]);

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        $data = $request->only([
            'name', 'category_id', 'brand', 'model', 'serial_number', 'property_number',
            'ip_address', 'mac_address', 'location', 'department', 'responsible_person',
            'purchase_date', 'warranty_end_date', 'price', 'vendor', 'status', 'description'
        ]);

        // 處理規格資訊
        $specifications = $request->input('specifications');
        if ($specifications && is_array($specifications)) {
            $data['specifications'] = json_encode($specifications);
        }

        // 檢查 IP 位址是否重複
        if (!empty($data['ip_address']) && $data['ip_address'] !== $equipment['ip_address']) {
            $existingEquipment = $this->db->fetch(
                "SELECT id FROM equipment WHERE ip_address = ? AND id != ?",
                [$data['ip_address'], $id]
            );

            if ($existingEquipment) {
                Response::error('該 IP 位址已被其他設備使用');
            }
        }

        // 檢查財產編號是否重複
        if (!empty($data['property_number']) && $data['property_number'] !== $equipment['property_number']) {
            $existingEquipment = $this->db->fetch(
                "SELECT id FROM equipment WHERE property_number = ? AND id != ?",
                [$data['property_number'], $id]
            );

            if ($existingEquipment) {
                Response::error('該財產編號已被其他設備使用');
            }
        }

        try {
            $this->db->beginTransaction();

            $this->db->update('equipment', $data, 'id = ?', [$id]);

            // 處理圖片更新
            $imageIds = $request->input('image_ids', []);
            if (!empty($imageIds)) {
                // 刪除現有關聯
                $this->db->delete('equipment_images', 'equipment_id = ?', [$id]);
                // 建立新關聯
                $this->attachImages($id, $imageIds);
            }

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('update', 'equipment', $id, $equipment, $data);

            // 獲取更新後的設備資訊
            $updatedEquipment = $this->db->fetch("
                SELECT e.*, c.name as category_name
                FROM equipment e
                LEFT JOIN equipment_categories c ON e.category_id = c.id
                WHERE e.id = ?
            ", [$id]);

            Response::updated($updatedEquipment, '設備更新成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('設備更新失敗：' . $e->getMessage());
        }
    }

    /**
     * 刪除設備
     */
    public function destroy(array $params): void
    {
        $id = (int)$params['id'];

        // 檢查設備是否存在
        $equipment = $this->db->fetch("SELECT * FROM equipment WHERE id = ?", [$id]);
        if (!$equipment) {
            Response::notFound('設備不存在');
        }

        // 檢查是否有相關的報修記錄
        $repairCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM repair_requests WHERE equipment_id = ?",
            [$id]
        )['count'];

        if ($repairCount > 0) {
            Response::error('該設備有相關的報修記錄，無法刪除');
        }

        try {
            $this->db->beginTransaction();

            // 刪除設備圖片檔案
            $images = $this->db->fetchAll(
                "SELECT file_path, thumbnail_path FROM equipment_images WHERE equipment_id = ?",
                [$id]
            );

            foreach ($images as $image) {
                if (file_exists($image['file_path'])) {
                    unlink($image['file_path']);
                }
                if ($image['thumbnail_path'] && file_exists($image['thumbnail_path'])) {
                    unlink($image['thumbnail_path']);
                }
            }

            // 刪除資料庫記錄（圖片記錄會因為外鍵約束自動刪除）
            $this->db->delete('equipment', 'id = ?', [$id]);

            $this->db->commit();

            // 記錄操作日誌
            $this->logAction('delete', 'equipment', $id, $equipment);

            Response::deleted('設備刪除成功');

        } catch (\Exception $e) {
            $this->db->rollback();
            Response::serverError('設備刪除失敗：' . $e->getMessage());
        }
    }

    /**
     * 獲取設備分類列表
     */
    public function categories(): void
    {
        $categories = $this->db->fetchAll("
            SELECT 
                id, name, description, parent_id, sort_order,
                (SELECT COUNT(*) FROM equipment WHERE category_id = equipment_categories.id) as equipment_count
            FROM equipment_categories 
            ORDER BY parent_id, sort_order, name
        ");

        Response::success($categories);
    }

    /**
     * Excel 匯出
     */
    public function export(): void
    {
        $request = new Request();
        $format = $request->input('format', 'xlsx'); // xlsx 或 csv

        // 獲取所有設備資料
        $equipment = $this->db->fetchAll("
            SELECT 
                e.name, e.brand, e.model, e.serial_number, e.property_number,
                e.ip_address, e.mac_address, e.location, e.department, e.responsible_person,
                e.purchase_date, e.warranty_end_date, e.price, e.vendor, e.status, e.description,
                c.name as category_name
            FROM equipment e
            LEFT JOIN equipment_categories c ON e.category_id = c.id
            ORDER BY e.name
        ");

        $filename = '設備清單_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filepath = 'exports/' . $filename;

        // 確保目錄存在
        if (!file_exists('exports')) {
            mkdir('exports', 0755, true);
        }

        if ($format === 'csv') {
            $this->exportToCsv($equipment, $filepath);
        } else {
            $this->exportToExcel($equipment, $filepath);
        }

        Response::download($filepath, $filename);
    }

    /**
     * Excel 匯入
     */
    public function import(): void
    {
        $request = new Request();

        if (!$request->hasFile('file')) {
            Response::error('請選擇要匯入的檔案');
        }

        $file = $request->file('file');
        $allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];

        if (!in_array($file['type'], $allowedTypes)) {
            Response::error('不支援的檔案格式');
        }

        try {
            $data = $this->parseImportFile($file);
            $results = $this->processImportData($data);

            Response::success($results, '匯入完成');

        } catch (\Exception $e) {
            Response::serverError('匯入失敗：' . $e->getMessage());
        }
    }

    /**
     * 關聯圖片到設備
     */
    private function attachImages(int $equipmentId, array $imageIds): void
    {
        foreach ($imageIds as $index => $imageId) {
            $this->db->update('equipment_images', [
                'equipment_id' => $equipmentId,
                'is_primary' => $index === 0 // 第一張設為主圖
            ], 'id = ?', [$imageId]);
        }
    }

    /**
     * 匯出到 CSV
     */
    private function exportToCsv(array $data, string $filepath): void
    {
        $fp = fopen($filepath, 'w');
        
        // 寫入 BOM 以支援中文
        fwrite($fp, "\xEF\xBB\xBF");

        // 寫入標題行
        $headers = [
            '設備名稱', '分類', '品牌', '型號', '序號', '財產編號',
            'IP位址', 'MAC位址', '位置', '部門', '負責人',
            '採購日期', '保固到期', '價格', '供應商', '狀態', '備註'
        ];
        fputcsv($fp, $headers);

        // 寫入資料
        foreach ($data as $row) {
            fputcsv($fp, [
                $row['name'], $row['category_name'], $row['brand'], $row['model'],
                $row['serial_number'], $row['property_number'], $row['ip_address'], $row['mac_address'],
                $row['location'], $row['department'], $row['responsible_person'],
                $row['purchase_date'], $row['warranty_end_date'], $row['price'],
                $row['vendor'], $row['status'], $row['description']
            ]);
        }

        fclose($fp);
    }

    /**
     * 匯出到 Excel
     */
    private function exportToExcel(array $data, string $filepath): void
    {
        // 這裡可以使用 PhpSpreadsheet 或其他 Excel 處理套件
        // 簡化版本先用 CSV
        $this->exportToCsv($data, $filepath);
    }

    /**
     * 解析匯入檔案
     */
    private function parseImportFile(array $file): array
    {
        // 這裡應該根據檔案類型進行解析
        // 簡化版本，假設是 CSV
        $data = [];
        $handle = fopen($file['tmp_name'], 'r');
        
        // 跳過標題行
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== FALSE) {
            $data[] = $row;
        }
        
        fclose($handle);
        return $data;
    }

    /**
     * 處理匯入資料
     */
    private function processImportData(array $data): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($data as $index => $row) {
            try {
                if (count($row) < 6) {
                    throw new \Exception('資料欄位不足');
                }

                $equipmentData = [
                    'name' => $row[0],
                    'brand' => $row[2],
                    'model' => $row[3],
                    'serial_number' => $row[4],
                    'property_number' => $row[5],
                    'ip_address' => $row[6] ?? null,
                    'mac_address' => $row[7] ?? null,
                    'location' => $row[8] ?? null,
                    'department' => $row[9] ?? null,
                    'responsible_person' => $row[10] ?? null,
                    'purchase_date' => !empty($row[11]) ? $row[11] : null,
                    'warranty_end_date' => !empty($row[12]) ? $row[12] : null,
                    'price' => !empty($row[13]) ? (float)$row[13] : null,
                    'vendor' => $row[14] ?? null,
                    'status' => $row[15] ?? 'active',
                    'description' => $row[16] ?? null,
                    'created_by' => $GLOBALS['current_user']['id']
                ];

                // 查找分類 ID
                if (!empty($row[1])) {
                    $category = $this->db->fetch(
                        "SELECT id FROM equipment_categories WHERE name = ?",
                        [$row[1]]
                    );
                    $equipmentData['category_id'] = $category['id'] ?? null;
                }

                $this->db->insert('equipment', $equipmentData);
                $results['success']++;

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "第 " . ($index + 2) . " 行：" . $e->getMessage();
            }
        }

        return $results;
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