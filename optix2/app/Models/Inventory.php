<?php
/**
 * Inventory Model
 *
 * Handles stock level management, adjustments, transfers, and history
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Inventory extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'inventory';

    /**
     * Disable soft deletes for inventory
     */
    protected bool $useSoftDeletes = false;

    /**
     * Get inventory by product and location
     *
     * @param int $productId Product ID
     * @param int $locationId Location ID
     * @return array|false
     */
    public function getByProductAndLocation(int $productId, int $locationId)
    {
        return $this->whereOne("product_id = ? AND location_id = ?", [$productId, $locationId]);
    }

    /**
     * Get inventory with product details
     *
     * @param int|null $locationId Location ID filter
     * @param bool $lowStockOnly Show only low stock items
     * @return array
     */
    public function getWithProductDetails(?int $locationId = null, bool $lowStockOnly = false): array
    {
        $sql = "SELECT i.*,
                p.sku,
                p.barcode,
                p.name as product_name,
                p.category,
                p.brand,
                p.cost_price,
                p.selling_price,
                l.name as location_name
                FROM {$this->table} i
                INNER JOIN products p ON i.product_id = p.id
                INNER JOIN locations l ON i.location_id = l.id
                WHERE p.deleted_at IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        if ($lowStockOnly) {
            $sql .= " AND i.quantity <= i.min_quantity";
        }

        $sql .= " ORDER BY p.name ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Adjust inventory level
     *
     * @param int $productId Product ID
     * @param int $locationId Location ID
     * @param int $quantityChange Quantity change (positive or negative)
     * @param string $adjustmentType Adjustment type
     * @param string|null $reason Reason for adjustment
     * @param int|null $userId User ID performing adjustment
     * @param int|null $referenceId Reference ID (transaction, etc.)
     * @return bool
     */
    public function adjustInventory(int $productId, int $locationId, int $quantityChange, string $adjustmentType, ?string $reason = null, ?int $userId = null, ?int $referenceId = null): bool
    {
        try {
            $this->beginTransaction();

            // Get current inventory
            $inventory = $this->getByProductAndLocation($productId, $locationId);

            if (!$inventory) {
                // Create inventory record if doesn't exist
                $this->db->insert($this->table, [
                    'product_id' => $productId,
                    'location_id' => $locationId,
                    'quantity' => max(0, $quantityChange),
                    'min_quantity' => 10,
                    'max_quantity' => 100,
                    'created_at' => date(DATETIME_FORMAT),
                    'updated_at' => date(DATETIME_FORMAT)
                ]);

                $quantityBefore = 0;
                $quantityAfter = max(0, $quantityChange);
            } else {
                $quantityBefore = $inventory['quantity'];
                $quantityAfter = max(0, $quantityBefore + $quantityChange);

                // Update inventory
                $this->db->update(
                    $this->table,
                    [
                        'quantity' => $quantityAfter,
                        'updated_at' => date(DATETIME_FORMAT)
                    ],
                    "product_id = ? AND location_id = ?",
                    [$productId, $locationId]
                );
            }

            // Record adjustment history
            $this->db->insert('inventory_adjustments', [
                'product_id' => $productId,
                'location_id' => $locationId,
                'user_id' => $userId,
                'adjustment_type' => $adjustmentType,
                'quantity_change' => $quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $reason,
                'reference_id' => $referenceId,
                'created_at' => date(DATETIME_FORMAT)
            ]);

            // Update last restocked timestamp if adding stock
            if ($quantityChange > 0 && in_array($adjustmentType, ['purchase', 'return', 'correction'])) {
                $this->db->update(
                    $this->table,
                    ['last_restocked_at' => date(DATETIME_FORMAT)],
                    "product_id = ? AND location_id = ?",
                    [$productId, $locationId]
                );
            }

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Transfer inventory between locations
     *
     * @param int $productId Product ID
     * @param int $fromLocationId From location ID
     * @param int $toLocationId To location ID
     * @param int $quantity Quantity to transfer
     * @param int|null $userId User ID performing transfer
     * @param string|null $reason Reason for transfer
     * @return bool
     */
    public function transferInventory(int $productId, int $fromLocationId, int $toLocationId, int $quantity, ?int $userId = null, ?string $reason = null): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        try {
            $this->beginTransaction();

            // Check if source has enough stock
            $sourceInventory = $this->getByProductAndLocation($productId, $fromLocationId);
            if (!$sourceInventory || $sourceInventory['quantity'] < $quantity) {
                $this->rollback();
                return false;
            }

            // Deduct from source location
            $this->adjustInventory($productId, $fromLocationId, -$quantity, 'transfer', $reason, $userId, $toLocationId);

            // Add to destination location
            $this->adjustInventory($productId, $toLocationId, $quantity, 'transfer', $reason, $userId, $fromLocationId);

            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * Get inventory adjustment history
     *
     * @param int|null $productId Product ID filter
     * @param int|null $locationId Location ID filter
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getAdjustmentHistory(?int $productId = null, ?int $locationId = null, ?string $startDate = null, ?string $endDate = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT ia.*,
                p.name as product_name,
                p.sku,
                l.name as location_name,
                CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM inventory_adjustments ia
                INNER JOIN products p ON ia.product_id = p.id
                INNER JOIN locations l ON ia.location_id = l.id
                LEFT JOIN users u ON ia.user_id = u.id
                WHERE 1=1";

        $params = [];

        if ($productId) {
            $sql .= " AND ia.product_id = ?";
            $params[] = $productId;
        }

        if ($locationId) {
            $sql .= " AND ia.location_id = ?";
            $params[] = $locationId;
        }

        if ($startDate && $endDate) {
            $sql .= " AND DATE(ia.created_at) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " ORDER BY ia.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params);
    }

    /**
     * Get low stock items
     *
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getLowStockItems(?int $locationId = null): array
    {
        $sql = "SELECT i.*,
                p.name as product_name,
                p.sku,
                p.category,
                l.name as location_name
                FROM {$this->table} i
                INNER JOIN products p ON i.product_id = p.id
                INNER JOIN locations l ON i.location_id = l.id
                WHERE i.quantity <= i.min_quantity
                AND p.is_active = TRUE
                AND p.deleted_at IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY i.quantity ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get inventory statistics
     *
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getStatistics(?int $locationId = null): array
    {
        $where = [];
        $params = [];

        if ($locationId) {
            $where[] = "i.location_id = ?";
            $params[] = $locationId;
        }

        $whereClause = !empty($where) ? " AND " . implode(" AND ", $where) : "";

        // Total items and quantity
        $totals = $this->db->selectOne(
            "SELECT COUNT(DISTINCT i.product_id) as total_products,
                    SUM(i.quantity) as total_quantity
             FROM {$this->table} i
             INNER JOIN products p ON i.product_id = p.id
             WHERE p.deleted_at IS NULL {$whereClause}",
            $params
        );

        // Low stock count
        $lowStock = $this->db->selectOne(
            "SELECT COUNT(*) as count
             FROM {$this->table} i
             INNER JOIN products p ON i.product_id = p.id
             WHERE i.quantity <= i.min_quantity
             AND p.deleted_at IS NULL {$whereClause}",
            $params
        );

        // Out of stock count
        $outOfStock = $this->db->selectOne(
            "SELECT COUNT(*) as count
             FROM {$this->table} i
             INNER JOIN products p ON i.product_id = p.id
             WHERE i.quantity = 0
             AND p.deleted_at IS NULL {$whereClause}",
            $params
        );

        // Total value
        $value = $this->db->selectOne(
            "SELECT SUM(p.cost_price * i.quantity) as total_cost,
                    SUM(p.selling_price * i.quantity) as total_retail
             FROM {$this->table} i
             INNER JOIN products p ON i.product_id = p.id
             WHERE p.deleted_at IS NULL {$whereClause}",
            $params
        );

        return [
            'total_products' => $totals['total_products'] ?? 0,
            'total_quantity' => $totals['total_quantity'] ?? 0,
            'low_stock_count' => $lowStock['count'] ?? 0,
            'out_of_stock_count' => $outOfStock['count'] ?? 0,
            'total_cost_value' => $value['total_cost'] ?? 0,
            'total_retail_value' => $value['total_retail'] ?? 0
        ];
    }

    /**
     * Set stock levels (min/max)
     *
     * @param int $productId Product ID
     * @param int $locationId Location ID
     * @param int $minQuantity Minimum quantity
     * @param int $maxQuantity Maximum quantity
     * @return bool
     */
    public function setStockLevels(int $productId, int $locationId, int $minQuantity, int $maxQuantity): bool
    {
        $inventory = $this->getByProductAndLocation($productId, $locationId);

        if (!$inventory) {
            return false;
        }

        $result = $this->db->update(
            $this->table,
            [
                'min_quantity' => $minQuantity,
                'max_quantity' => $maxQuantity,
                'updated_at' => date(DATETIME_FORMAT)
            ],
            "product_id = ? AND location_id = ?",
            [$productId, $locationId]
        );

        return $result > 0;
    }

    /**
     * Get inventory value by location
     *
     * @param int $locationId Location ID
     * @return array
     */
    public function getInventoryValue(int $locationId): array
    {
        $sql = "SELECT
                SUM(p.cost_price * i.quantity) as total_cost,
                SUM(p.selling_price * i.quantity) as total_retail,
                SUM((p.selling_price - p.cost_price) * i.quantity) as potential_profit
                FROM {$this->table} i
                INNER JOIN products p ON i.product_id = p.id
                WHERE i.location_id = ?
                AND p.deleted_at IS NULL";

        return $this->db->selectOne($sql, [$locationId]);
    }
}
