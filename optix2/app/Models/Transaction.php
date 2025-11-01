<?php
/**
 * Transaction Model
 *
 * Handles transaction/sales data operations
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Transaction extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'transactions';

    /**
     * Generate unique transaction number
     *
     * @return string
     */
    public function generateTransactionNumber(): string
    {
        $prefix = 'TXN';
        $date = date('Ymd');
        $lastTransaction = $this->db->selectOne(
            "SELECT transaction_number FROM {$this->table}
             WHERE transaction_number LIKE '{$prefix}{$date}%'
             ORDER BY id DESC LIMIT 1"
        );

        if ($lastTransaction) {
            $lastNumber = (int)substr($lastTransaction['transaction_number'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create transaction with items
     *
     * @param array $transactionData Transaction data
     * @param array $items Transaction items
     * @return int Transaction ID
     */
    public function createWithItems(array $transactionData, array $items): int
    {
        $this->db->beginTransaction();

        try {
            // Generate transaction number
            $transactionData['transaction_number'] = $this->generateTransactionNumber();

            // Create transaction
            $transactionId = $this->create($transactionData);

            // Create transaction items
            foreach ($items as $item) {
                $item['transaction_id'] = $transactionId;
                $this->db->insert('transaction_items', $item);

                // Update inventory
                if (isset($item['product_id']) && $transactionData['location_id']) {
                    $this->updateInventory(
                        $item['product_id'],
                        $transactionData['location_id'],
                        -$item['quantity'],
                        'sale',
                        $transactionId
                    );
                }
            }

            $this->db->commit();
            return $transactionId;
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get transaction with items
     *
     * @param int $id Transaction ID
     * @return array|false
     */
    public function getWithItems(int $id)
    {
        $transaction = $this->find($id);
        if (!$transaction) {
            return false;
        }

        // Get transaction items
        $transaction['items'] = $this->db->select(
            "SELECT ti.*, p.name as product_name
             FROM transaction_items ti
             LEFT JOIN products p ON ti.product_id = p.id
             WHERE ti.transaction_id = ?",
            [$id]
        );

        // Get payments
        $transaction['payments'] = $this->db->select(
            "SELECT * FROM payments WHERE transaction_id = ? ORDER BY payment_date DESC",
            [$id]
        );

        // Get patient info if exists
        if ($transaction['patient_id']) {
            $transaction['patient'] = $this->db->selectOne(
                "SELECT id, patient_number, first_name, last_name, email, phone
                 FROM patients WHERE id = ?",
                [$transaction['patient_id']]
            );
        }

        // Get cashier info
        $transaction['cashier'] = $this->db->selectOne(
            "SELECT id, first_name, last_name FROM users WHERE id = ?",
            [$transaction['cashier_id']]
        );

        return $transaction;
    }

    /**
     * Get daily sales report
     *
     * @param string $date Date
     * @param int|null $locationId Location ID
     * @return array
     */
    public function getDailySales(string $date, ?int $locationId = null): array
    {
        $sql = "SELECT
                    COUNT(*) as transaction_count,
                    COALESCE(SUM(subtotal), 0) as subtotal,
                    COALESCE(SUM(tax), 0) as tax,
                    COALESCE(SUM(discount), 0) as discount,
                    COALESCE(SUM(total), 0) as total,
                    COALESCE(SUM(amount_paid), 0) as amount_paid
                FROM {$this->table}
                WHERE transaction_date = ? AND status = 'completed' AND deleted_at IS NULL";

        $params = [$date];

        if ($locationId) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        return $this->db->selectOne($sql, $params);
    }

    /**
     * Update inventory on transaction
     *
     * @param int $productId Product ID
     * @param int $locationId Location ID
     * @param int $quantityChange Quantity change (negative for sale)
     * @param string $adjustmentType Adjustment type
     * @param int $referenceId Reference transaction ID
     * @return void
     */
    private function updateInventory(int $productId, int $locationId, int $quantityChange, string $adjustmentType, int $referenceId): void
    {
        // Get current inventory
        $inventory = $this->db->selectOne(
            "SELECT * FROM inventory WHERE product_id = ? AND location_id = ?",
            [$productId, $locationId]
        );

        if ($inventory) {
            $quantityBefore = $inventory['quantity'];
            $quantityAfter = $quantityBefore + $quantityChange;

            // Update inventory
            $this->db->update(
                'inventory',
                ['quantity' => $quantityAfter],
                'product_id = ? AND location_id = ?',
                [$productId, $locationId]
            );

            // Log adjustment
            $this->db->insert('inventory_adjustments', [
                'product_id' => $productId,
                'location_id' => $locationId,
                'adjustment_type' => $adjustmentType,
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_id' => $referenceId,
                'created_at' => date(DATETIME_FORMAT),
            ]);
        }
    }
}
