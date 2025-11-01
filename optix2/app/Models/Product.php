<?php
/**
 * Product Model
 *
 * Handles product data operations including CRUD, search by barcode/SKU, and low stock alerts
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Product extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'products';

    /**
     * Find product by SKU
     *
     * @param string $sku SKU
     * @return array|false
     */
    public function findBySku(string $sku)
    {
        return $this->whereOne("sku = ?", [$sku]);
    }

    /**
     * Find product by barcode
     *
     * @param string $barcode Barcode
     * @return array|false
     */
    public function findByBarcode(string $barcode)
    {
        return $this->whereOne("barcode = ?", [$barcode]);
    }

    /**
     * Search products
     *
     * @param string $term Search term
     * @param string|null $category Category filter
     * @param bool $activeOnly Active products only
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function search(string $term, ?string $category = null, bool $activeOnly = true, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                s.name as supplier_name
                FROM {$this->table} p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ? OR p.description LIKE ?)
                AND p.{$this->deletedAt} IS NULL";

        $searchTerm = "%{$term}%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }

        if ($activeOnly) {
            $sql .= " AND p.is_active = TRUE";
        }

        $sql .= " ORDER BY p.name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params);
    }

    /**
     * Get products by category
     *
     * @param string $category Category
     * @param bool $activeOnly Active products only
     * @return array
     */
    public function getByCategory(string $category, bool $activeOnly = true): array
    {
        $where = "category = ?";
        $params = [$category];

        if ($activeOnly) {
            $where .= " AND is_active = TRUE";
        }

        return $this->where($where, $params);
    }

    /**
     * Get products with inventory data
     *
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getWithInventory(?int $locationId = null): array
    {
        $sql = "SELECT p.*,
                s.name as supplier_name,
                i.quantity,
                i.min_quantity,
                i.max_quantity,
                i.last_restocked_at,
                l.name as location_name
                FROM {$this->table} p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN inventory i ON p.id = i.product_id
                LEFT JOIN locations l ON i.location_id = l.id
                WHERE p.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY p.name ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get low stock products
     *
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getLowStock(?int $locationId = null): array
    {
        $sql = "SELECT p.*,
                i.quantity,
                i.min_quantity,
                i.location_id,
                l.name as location_name,
                s.name as supplier_name
                FROM {$this->table} p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id
                LEFT JOIN locations l ON i.location_id = l.id
                WHERE i.quantity <= i.min_quantity
                AND p.is_active = TRUE
                AND p.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY i.quantity ASC, p.name ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get out of stock products
     *
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getOutOfStock(?int $locationId = null): array
    {
        $sql = "SELECT p.*,
                i.quantity,
                i.location_id,
                l.name as location_name,
                s.name as supplier_name
                FROM {$this->table} p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                INNER JOIN inventory i ON p.id = i.product_id
                LEFT JOIN locations l ON i.location_id = l.id
                WHERE i.quantity = 0
                AND p.is_active = TRUE
                AND p.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY p.name ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get product with full details
     *
     * @param int $id Product ID
     * @return array|false
     */
    public function getWithDetails(int $id)
    {
        $sql = "SELECT p.*,
                s.name as supplier_name,
                s.phone as supplier_phone,
                s.email as supplier_email
                FROM {$this->table} p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE p.id = ? AND p.{$this->deletedAt} IS NULL";

        return $this->db->selectOne($sql, [$id]);
    }

    /**
     * Get best selling products
     *
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @param int $limit Limit
     * @return array
     */
    public function getBestSelling(?string $startDate = null, ?string $endDate = null, int $limit = 10): array
    {
        $sql = "SELECT p.*,
                SUM(ti.quantity) as total_sold,
                SUM(ti.line_total) as total_revenue
                FROM {$this->table} p
                INNER JOIN transaction_items ti ON p.id = ti.product_id
                INNER JOIN transactions t ON ti.transaction_id = t.id
                WHERE t.status = 'completed'
                AND p.{$this->deletedAt} IS NULL";

        $params = [];

        if ($startDate && $endDate) {
            $sql .= " AND t.transaction_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $sql .= " GROUP BY p.id
                  ORDER BY total_sold DESC
                  LIMIT ?";
        $params[] = $limit;

        return $this->db->select($sql, $params);
    }

    /**
     * Get product statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $total = $this->count("is_active = TRUE");
        $inactive = $this->count("is_active = FALSE");

        $categoryCounts = $this->db->select(
            "SELECT category, COUNT(*) as count
             FROM {$this->table}
             WHERE {$this->deletedAt} IS NULL
             GROUP BY category
             ORDER BY count DESC"
        );

        $totalValue = $this->db->selectOne(
            "SELECT SUM(p.cost_price * i.quantity) as total_cost,
                    SUM(p.selling_price * i.quantity) as total_retail
             FROM {$this->table} p
             INNER JOIN inventory i ON p.id = i.product_id
             WHERE p.{$this->deletedAt} IS NULL"
        );

        return [
            'total_active' => $total,
            'total_inactive' => $inactive,
            'by_category' => $categoryCounts,
            'total_cost_value' => $totalValue['total_cost'] ?? 0,
            'total_retail_value' => $totalValue['total_retail'] ?? 0
        ];
    }

    /**
     * Update product stock
     *
     * @param int $productId Product ID
     * @param int $locationId Location ID
     * @param int $quantity Quantity to add (negative to subtract)
     * @return bool
     */
    public function updateStock(int $productId, int $locationId, int $quantity): bool
    {
        $sql = "UPDATE inventory
                SET quantity = quantity + ?,
                    updated_at = ?
                WHERE product_id = ? AND location_id = ?";

        $stmt = $this->db->query($sql, [$quantity, date(DATETIME_FORMAT), $productId, $locationId]);
        return $stmt->rowCount() > 0;
    }
}
