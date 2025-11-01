<?php
/**
 * Lab Order Model - Manages lab orders for eyewear and lenses
 */

namespace App\Models;

class LabOrder extends BaseModel
{
    protected string $table = 'lab_orders';

    public function generateOrderNumber(): string
    {
        $prefix = 'LAB';
        $date = date('Ymd');
        $last = $this->db->selectOne(
            "SELECT order_number FROM {$this->table} WHERE order_number LIKE '{$prefix}{$date}%' ORDER BY id DESC LIMIT 1"
        );

        $num = $last ? (int)substr($last['order_number'], -4) + 1 : 1;
        return $prefix . $date . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function getByPatientId(int $patientId): array
    {
        $sql = "SELECT lo.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} lo
                LEFT JOIN patients p ON lo.patient_id = p.id
                LEFT JOIN users u ON lo.provider_id = u.id
                WHERE lo.patient_id = ? AND lo.{$this->deletedAt} IS NULL
                ORDER BY lo.order_date DESC";

        return $this->db->select($sql, [$patientId]);
    }

    public function getPendingOrders(?int $locationId = null): array
    {
        $sql = "SELECT lo.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_number,
                l.name as location_name
                FROM {$this->table} lo
                LEFT JOIN patients p ON lo.patient_id = p.id
                LEFT JOIN locations l ON lo.location_id = l.id
                WHERE lo.status IN ('pending', 'submitted', 'in_production')
                AND lo.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND lo.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY lo.order_date ASC";

        return $this->db->select($sql, $params);
    }

    public function getOverdueOrders(): array
    {
        $sql = "SELECT lo.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.phone as patient_phone
                FROM {$this->table} lo
                LEFT JOIN patients p ON lo.patient_id = p.id
                WHERE lo.expected_delivery < CURDATE()
                AND lo.status NOT IN ('completed', 'delivered', 'cancelled')
                AND lo.{$this->deletedAt} IS NULL
                ORDER BY lo.expected_delivery ASC";

        return $this->db->select($sql);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $data = ['status' => $status];

        if ($status === 'delivered') {
            $data['actual_delivery'] = date('Y-m-d');
        }

        return $this->update($id, $data) > 0;
    }

    public function getStatistics(?int $locationId = null): array
    {
        $where = $locationId ? "location_id = {$locationId}" : null;

        $statusCounts = $this->db->select(
            "SELECT status, COUNT(*) as count
             FROM {$this->table}
             WHERE " . ($where ? "{$where} AND " : "") . "{$this->deletedAt} IS NULL
             GROUP BY status"
        );

        $total = $this->count($where);

        return [
            'total' => $total,
            'by_status' => $statusCounts
        ];
    }
}
