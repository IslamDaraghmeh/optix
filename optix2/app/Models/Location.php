<?php
/**
 * Location Model - Manages clinic locations
 */

namespace App\Models;

class Location extends BaseModel
{
    protected string $table = 'locations';

    public function getActive(): array
    {
        return $this->where("is_active = TRUE");
    }

    public function getWithUserCount(): array
    {
        $sql = "SELECT l.*, COUNT(u.id) as user_count
                FROM {$this->table} l
                LEFT JOIN users u ON l.id = u.location_id AND u.deleted_at IS NULL
                WHERE l.{$this->deletedAt} IS NULL
                GROUP BY l.id
                ORDER BY l.name ASC";

        return $this->db->select($sql);
    }

    public function getStatistics(int $locationId): array
    {
        $patients = $this->db->selectOne(
            "SELECT COUNT(DISTINCT patient_id) as count
             FROM appointments
             WHERE location_id = ? AND deleted_at IS NULL",
            [$locationId]
        );

        $appointments = $this->db->selectOne(
            "SELECT COUNT(*) as count
             FROM appointments
             WHERE location_id = ? AND deleted_at IS NULL",
            [$locationId]
        );

        $revenue = $this->db->selectOne(
            "SELECT SUM(total) as total
             FROM transactions
             WHERE location_id = ? AND status = 'completed' AND deleted_at IS NULL",
            [$locationId]
        );

        return [
            'patient_count' => $patients['count'] ?? 0,
            'appointment_count' => $appointments['count'] ?? 0,
            'total_revenue' => $revenue['total'] ?? 0
        ];
    }
}
