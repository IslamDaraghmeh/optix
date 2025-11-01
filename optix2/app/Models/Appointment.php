<?php
/**
 * Appointment Model
 *
 * Handles appointment data operations
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Appointment extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'appointments';

    /**
     * Get appointments by date and provider
     *
     * @param string $date Date
     * @param int|null $providerId Provider ID
     * @return array
     */
    public function getByDate(string $date, ?int $providerId = null): array
    {
        $sql = "SELECT a.*,
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                       p.phone as patient_phone
                FROM {$this->table} a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN users u ON a.provider_id = u.id
                WHERE a.appointment_date = ? AND a.deleted_at IS NULL";

        $params = [$date];

        if ($providerId) {
            $sql .= " AND a.provider_id = ?";
            $params[] = $providerId;
        }

        $sql .= " ORDER BY a.appointment_time ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Check time slot availability
     *
     * @param string $date Date
     * @param string $time Time
     * @param int $providerId Provider ID
     * @param int $duration Duration in minutes
     * @param int|null $excludeId Exclude appointment ID (for updates)
     * @return bool
     */
    public function checkAvailability(string $date, string $time, int $providerId, int $duration = 30, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE appointment_date = ?
                  AND provider_id = ?
                  AND status NOT IN ('cancelled', 'no_show')
                  AND deleted_at IS NULL
                  AND (
                    (appointment_time <= ? AND DATE_ADD(CONCAT(appointment_date, ' ', appointment_time), INTERVAL duration MINUTE) > ?)
                    OR
                    (appointment_time < DATE_ADD(?, INTERVAL ? MINUTE) AND appointment_time >= ?)
                  )";

        $params = [$date, $providerId, $time, $time, $time, $duration, $time];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->selectOne($sql, $params);
        return $result['count'] == 0;
    }

    /**
     * Get upcoming appointments for provider
     *
     * @param int $providerId Provider ID
     * @param int $limit Limit
     * @return array
     */
    public function getUpcoming(int $providerId, int $limit = 10): array
    {
        $sql = "SELECT a.*,
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.phone as patient_phone
                FROM {$this->table} a
                LEFT JOIN patients p ON a.patient_id = p.id
                WHERE a.provider_id = ?
                  AND CONCAT(a.appointment_date, ' ', a.appointment_time) >= NOW()
                  AND a.status NOT IN ('cancelled', 'completed', 'no_show')
                  AND a.deleted_at IS NULL
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT ?";

        return $this->db->select($sql, [$providerId, $limit]);
    }

    /**
     * Get appointments needing reminders
     *
     * @param int $hoursAhead Hours ahead to check
     * @return array
     */
    public function getNeedingReminders(int $hoursAhead = 24): array
    {
        $sql = "SELECT a.*,
                       CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.email as patient_email,
                       p.phone as patient_phone,
                       CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN users u ON a.provider_id = u.id
                WHERE a.reminder_sent = FALSE
                  AND a.status IN ('scheduled', 'confirmed')
                  AND CONCAT(a.appointment_date, ' ', a.appointment_time) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? HOUR)
                  AND a.deleted_at IS NULL";

        return $this->db->select($sql, [$hoursAhead]);
    }
}
