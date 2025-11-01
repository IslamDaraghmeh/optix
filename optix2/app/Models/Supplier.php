<?php
/**
 * Supplier Model - Manages product suppliers
 */

namespace App\Models;

class Supplier extends BaseModel
{
    protected string $table = 'suppliers';

    public function getActive(): array
    {
        return $this->where("is_active = TRUE");
    }

    public function getWithProductCount(): array
    {
        $sql = "SELECT s.*, COUNT(p.id) as product_count
                FROM {$this->table} s
                LEFT JOIN products p ON s.id = p.supplier_id AND p.deleted_at IS NULL
                WHERE s.{$this->deletedAt} IS NULL
                GROUP BY s.id
                ORDER BY s.name ASC";

        return $this->db->select($sql);
    }

    public function searchByName(string $name): array
    {
        return $this->where("name LIKE ? AND is_active = TRUE", ["%{$name}%"]);
    }
}
