<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Response;
use PDO;
use Exception;

class PaymentController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * 獲取所有付款記錄
     */
    public function index()
    {
        try {
            $sql = "SELECT * FROM payments ORDER BY item_number, end_date DESC";
            $stmt = $this->db->query($sql);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 格式化數據
            foreach ($payments as &$payment) {
                $payment['amount_usd'] = $payment['amount_usd'] ? (float)$payment['amount_usd'] : null;
                $payment['amount_twd'] = $payment['amount_twd'] ? (int)$payment['amount_twd'] : null;
                $payment['item_number'] = (int)$payment['item_number'];
            }

            Response::json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取付款記錄失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 創建新付款記錄
     */
    public function store()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // 驗證必要欄位
            $required = ['item_number', 'name', 'start_date', 'end_date', 'payment_method'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    Response::json([
                        'success' => false,
                        'message' => '缺少必要欄位',
                        'errors' => ["欄位 {$field} 為必填"]
                    ], 400);
                    return;
                }
            }

            // 檢查項次是否已存在
            $checkSql = "SELECT id FROM payments WHERE item_number = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$data['item_number']]);
            if ($checkStmt->fetch()) {
                Response::json([
                    'success' => false,
                    'message' => '項次已存在',
                    'errors' => ['此項次編號已被使用，請使用不同的項次編號']
                ], 400);
                return;
            }

            $sql = "INSERT INTO payments (
                        item_number, name, vendor, purchase_url, account_info,
                        start_date, end_date, billing_cycle, payment_method,
                        amount_usd, amount_twd, status, notes, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['item_number'],
                $data['name'],
                $data['vendor'] ?? '',
                $data['purchase_url'] ?? '',
                $data['account_info'] ?? '',
                $data['start_date'],
                $data['end_date'],
                $data['billing_cycle'] ?? '年',
                $data['payment_method'],
                $data['amount_usd'] ?? null,
                $data['amount_twd'] ?? null,
                $data['status'] ?? 'active',
                $data['notes'] ?? ''
            ]);

            if ($result) {
                Response::json([
                    'success' => true,
                    'message' => '付款記錄創建成功',
                    'data' => ['id' => $this->db->lastInsertId()]
                ], 201);
            } else {
                throw new Exception('付款記錄創建失敗');
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '付款記錄創建失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 獲取特定付款記錄
     */
    public function show($id)
    {
        try {
            $sql = "SELECT * FROM payments WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                Response::json([
                    'success' => false,
                    'message' => '付款記錄不存在'
                ], 404);
                return;
            }

            // 格式化數據
            $payment['amount_usd'] = $payment['amount_usd'] ? (float)$payment['amount_usd'] : null;
            $payment['amount_twd'] = $payment['amount_twd'] ? (int)$payment['amount_twd'] : null;
            $payment['item_number'] = (int)$payment['item_number'];

            Response::json([
                'success' => true,
                'data' => $payment
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取付款記錄失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 更新付款記錄
     */
    public function update($id)
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // 檢查記錄是否存在
            $checkSql = "SELECT id FROM payments WHERE id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$id]);
            if (!$checkStmt->fetch()) {
                Response::json([
                    'success' => false,
                    'message' => '付款記錄不存在'
                ], 404);
                return;
            }

            // 檢查項次是否被其他記錄使用
            if (!empty($data['item_number'])) {
                $checkSql = "SELECT id FROM payments WHERE item_number = ? AND id != ?";
                $checkStmt = $this->db->prepare($checkSql);
                $checkStmt->execute([$data['item_number'], $id]);
                if ($checkStmt->fetch()) {
                    Response::json([
                        'success' => false,
                        'message' => '項次已存在',
                        'errors' => ['此項次編號已被其他記錄使用']
                    ], 400);
                    return;
                }
            }

            $sql = "UPDATE payments SET 
                        item_number = ?, name = ?, vendor = ?, purchase_url = ?, account_info = ?,
                        start_date = ?, end_date = ?, billing_cycle = ?, payment_method = ?,
                        amount_usd = ?, amount_twd = ?, status = ?, notes = ?, updated_at = NOW()
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $data['item_number'],
                $data['name'],
                $data['vendor'] ?? '',
                $data['purchase_url'] ?? '',
                $data['account_info'] ?? '',
                $data['start_date'],
                $data['end_date'],
                $data['billing_cycle'] ?? '年',
                $data['payment_method'],
                $data['amount_usd'] ?? null,
                $data['amount_twd'] ?? null,
                $data['status'] ?? 'active',
                $data['notes'] ?? '',
                $id
            ]);

            if ($result) {
                Response::json([
                    'success' => true,
                    'message' => '付款記錄更新成功'
                ]);
            } else {
                throw new Exception('付款記錄更新失敗');
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '付款記錄更新失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 刪除付款記錄
     */
    public function destroy($id)
    {
        try {
            // 檢查記錄是否存在
            $checkSql = "SELECT name FROM payments WHERE id = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$id]);
            $payment = $checkStmt->fetch();
            
            if (!$payment) {
                Response::json([
                    'success' => false,
                    'message' => '付款記錄不存在'
                ], 404);
                return;
            }

            $sql = "DELETE FROM payments WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);

            if ($result) {
                Response::json([
                    'success' => true,
                    'message' => '付款記錄刪除成功'
                ]);
            } else {
                throw new Exception('付款記錄刪除失敗');
            }
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '付款記錄刪除失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * 獲取即將到期的付款記錄
     */
    public function getExpiring()
    {
        try {
            $sql = "SELECT * FROM payments 
                    WHERE status = 'active' 
                    AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                    ORDER BY end_date ASC";
            
            $stmt = $this->db->query($sql);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 格式化數據
            foreach ($payments as &$payment) {
                $payment['amount_usd'] = $payment['amount_usd'] ? (float)$payment['amount_usd'] : null;
                $payment['amount_twd'] = $payment['amount_twd'] ? (int)$payment['amount_twd'] : null;
                $payment['item_number'] = (int)$payment['item_number'];
                
                // 計算剩餘天數
                $endDate = new \DateTime($payment['end_date']);
                $now = new \DateTime();
                $payment['days_remaining'] = $now->diff($endDate)->days;
            }

            Response::json([
                'success' => true,
                'data' => $payments
            ]);
        } catch (Exception $e) {
            Response::json([
                'success' => false,
                'message' => '獲取即將到期付款記錄失敗',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}