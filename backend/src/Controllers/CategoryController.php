<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use PDO;
use Exception;

class CategoryController 
{
    private $db;

    public function __construct() 
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取所有設備分類
     */
    public function index() 
    {
        try {
            // 暫時不計算設備數量，因為equipment表可能不存在
            $sql = "SELECT *, 0 as equipment_count FROM equipment_categories ORDER BY id ASC";
            
            $stmt = $this->db->query($sql);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            Response::json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取分類列表失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 創建新分類
     */
    public function store() 
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || empty(trim($input['name']))) {
                Response::json([
                    'success' => false,
                    'message' => '分類名稱不能為空',
                    'errors' => ['name' => '分類名稱是必填項目']
                ], 400);
                return;
            }

            $name = trim($input['name']);

            // 檢查分類名稱是否已存在
            $existingCategory = $this->db->query(
                "SELECT id FROM equipment_categories WHERE name = ?", 
                [$name]
            );

            if (!empty($existingCategory)) {
                Response::json([
                    'success' => false,
                    'message' => '分類名稱已存在',
                    'errors' => ['name' => '此分類名稱已被使用']
                ], 400);
                return;
            }

            // 插入新分類
            $categoryId = $this->db->insert('equipment_categories', [
                'name' => $name,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($categoryId) {
                $category = $this->db->query(
                    "SELECT *, 0 as equipment_count FROM equipment_categories WHERE id = ?", 
                    [$categoryId]
                )[0];

                Response::json([
                    'success' => true,
                    'message' => '分類創建成功',
                    'data' => $category
                ], 201);
            } else {
                Response::json([
                    'success' => false,
                    'message' => '分類創建失敗',
                    'errors' => ['database' => '資料庫操作失敗']
                ], 500);
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '分類創建失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 更新分類
     */
    public function update($id) 
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || empty(trim($input['name']))) {
                Response::json([
                    'success' => false,
                    'message' => '分類名稱不能為空',
                    'errors' => ['name' => '分類名稱是必填項目']
                ], 400);
                return;
            }

            $name = trim($input['name']);

            // 檢查分類是否存在
            $existingCategory = $this->db->query(
                "SELECT id FROM equipment_categories WHERE id = ?", 
                [$id]
            );

            if (empty($existingCategory)) {
                Response::json([
                    'success' => false,
                    'message' => '分類不存在',
                    'errors' => ['id' => '找不到指定的分類']
                ], 404);
                return;
            }

            // 檢查分類名稱是否已被其他分類使用
            $duplicateCategory = $this->db->query(
                "SELECT id FROM equipment_categories WHERE name = ? AND id != ?", 
                [$name, $id]
            );

            if (!empty($duplicateCategory)) {
                Response::json([
                    'success' => false,
                    'message' => '分類名稱已存在',
                    'errors' => ['name' => '此分類名稱已被使用']
                ], 400);
                return;
            }

            // 更新分類
            $updated = $this->db->update('equipment_categories', [
                'name' => $name,
                'updated_at' => date('Y-m-d H:i:s')
            ], "id = ?", [$id]);

            if ($updated) {
                $category = $this->db->query(
                    "SELECT *, 0 as equipment_count FROM equipment_categories WHERE id = ?", 
                    [$id]
                )[0];

                Response::json([
                    'success' => true,
                    'message' => '分類更新成功',
                    'data' => $category
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => '分類更新失敗',
                    'errors' => ['database' => '資料庫操作失敗']
                ], 500);
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '分類更新失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 刪除分類
     */
    public function destroy($id) 
    {
        try {
            // 檢查分類是否存在
            $existingCategory = $this->db->query(
                "SELECT id FROM equipment_categories WHERE id = ?", 
                [$id]
            );

            if (empty($existingCategory)) {
                Response::json([
                    'success' => false,
                    'message' => '分類不存在',
                    'errors' => ['id' => '找不到指定的分類']
                ], 404);
                return;
            }

            // 暫時跳過設備檢查，因為equipment表可能不存在
            // TODO: 當equipment表創建後，重新啟用此檢查
            /*
            $equipmentCount = $this->db->query(
                "SELECT COUNT(*) as count FROM equipment WHERE category_id = ?", 
                [$id]
            )[0]['count'];

            if ($equipmentCount > 0) {
                Response::json([
                    'success' => false,
                    'message' => '無法刪除分類',
                    'errors' => ['equipment' => '該分類下還有 ' . $equipmentCount . ' 個設備，請先移除或重新分類設備']
                ], 400);
                return;
            }
            */

            // 刪除分類
            $deleted = $this->db->delete('equipment_categories', "id = ?", [$id]);

            if ($deleted) {
                Response::json([
                    'success' => true,
                    'message' => '分類刪除成功'
                ]);
            } else {
                Response::json([
                    'success' => false,
                    'message' => '分類刪除失敗',
                    'errors' => ['database' => '資料庫操作失敗']
                ], 500);
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '分類刪除失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 獲取單一分類詳情
     */
    public function show($id) 
    {
        try {
            $category = $this->db->query(
                "SELECT *, 0 as equipment_count FROM equipment_categories WHERE id = ?", 
                [$id]
            );

            if (empty($category)) {
                Response::json([
                    'success' => false,
                    'message' => '分類不存在',
                    'errors' => ['id' => '找不到指定的分類']
                ], 404);
                return;
            }

            Response::json([
                'success' => true,
                'data' => $category[0]
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取分類失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}