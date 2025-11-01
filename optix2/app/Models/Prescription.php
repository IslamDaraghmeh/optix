<?php
/**
 * Prescription Model
 *
 * Handles prescription data operations including CRUD and expiration checking
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Prescription extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'prescriptions';

    /**
     * Get prescriptions by patient ID
     *
     * @param int $patientId Patient ID
     * @param int|null $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByPatientId(int $patientId, ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                e.exam_date
                FROM {$this->table} p
                LEFT JOIN users u ON p.provider_id = u.id
                LEFT JOIN examinations e ON p.examination_id = e.id
                WHERE p.patient_id = ? AND p.{$this->deletedAt} IS NULL
                ORDER BY p.prescription_date DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->select($sql, [$patientId]);
    }

    /**
     * Get prescription with related data
     *
     * @param int $id Prescription ID
     * @return array|false
     */
    public function getWithRelations(int $id)
    {
        $sql = "SELECT p.*,
                CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                pt.patient_number,
                pt.date_of_birth,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                u.role as provider_role,
                e.exam_date,
                e.id as examination_id
                FROM {$this->table} p
                LEFT JOIN patients pt ON p.patient_id = pt.id
                LEFT JOIN users u ON p.provider_id = u.id
                LEFT JOIN examinations e ON p.examination_id = e.id
                WHERE p.id = ? AND p.{$this->deletedAt} IS NULL";

        return $this->db->selectOne($sql, [$id]);
    }

    /**
     * Get active prescriptions by patient ID
     *
     * @param int $patientId Patient ID
     * @return array
     */
    public function getActivePrescriptions(int $patientId): array
    {
        $sql = "SELECT p.*,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.provider_id = u.id
                WHERE p.patient_id = ?
                AND p.status = 'active'
                AND (p.expiration_date IS NULL OR p.expiration_date >= CURDATE())
                AND p.{$this->deletedAt} IS NULL
                ORDER BY p.prescription_date DESC";

        return $this->db->select($sql, [$patientId]);
    }

    /**
     * Get latest prescription by patient ID and type
     *
     * @param int $patientId Patient ID
     * @param string|null $type Prescription type
     * @return array|false
     */
    public function getLatestByPatientAndType(int $patientId, ?string $type = null)
    {
        $sql = "SELECT p.*,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.provider_id = u.id
                WHERE p.patient_id = ? AND p.{$this->deletedAt} IS NULL";

        $params = [$patientId];

        if ($type) {
            $sql .= " AND p.prescription_type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY p.prescription_date DESC LIMIT 1";

        return $this->db->selectOne($sql, $params);
    }

    /**
     * Check if prescription is expired
     *
     * @param int $id Prescription ID
     * @return bool
     */
    public function isExpired(int $id): bool
    {
        $prescription = $this->find($id);

        if (!$prescription) {
            return true;
        }

        if ($prescription['status'] !== 'active') {
            return true;
        }

        if ($prescription['expiration_date'] && strtotime($prescription['expiration_date']) < strtotime('today')) {
            return true;
        }

        return false;
    }

    /**
     * Get expiring prescriptions
     *
     * @param int $days Days until expiration
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getExpiringPrescriptions(int $days = 30, ?int $locationId = null): array
    {
        $sql = "SELECT p.*,
                CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                pt.patient_number,
                pt.email,
                pt.phone,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} p
                LEFT JOIN patients pt ON p.patient_id = pt.id
                LEFT JOIN users u ON p.provider_id = u.id
                LEFT JOIN examinations e ON p.examination_id = e.id
                WHERE p.status = 'active'
                AND p.expiration_date IS NOT NULL
                AND p.expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND p.{$this->deletedAt} IS NULL";

        $params = [$days];

        if ($locationId) {
            $sql .= " AND e.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY p.expiration_date ASC";

        return $this->db->select($sql, $params);
    }

    /**
     * Update prescription status
     *
     * @param int $id Prescription ID
     * @param string $status New status
     * @return int Number of affected rows
     */
    public function updateStatus(int $id, string $status): int
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Mark expired prescriptions
     *
     * @return int Number of prescriptions marked as expired
     */
    public function markExpiredPrescriptions(): int
    {
        $sql = "UPDATE {$this->table}
                SET status = 'expired', updated_at = ?
                WHERE status = 'active'
                AND expiration_date < CURDATE()
                AND {$this->deletedAt} IS NULL";

        $stmt = $this->db->query($sql, [date(DATETIME_FORMAT)]);
        return $stmt->rowCount();
    }

    /**
     * Copy prescription data from another prescription
     *
     * @param int $sourceId Source prescription ID
     * @param int $patientId Patient ID
     * @param int $providerId Provider ID
     * @param int|null $examinationId Examination ID
     * @return int New prescription ID
     */
    public function copyFromPrevious(int $sourceId, int $patientId, int $providerId, ?int $examinationId = null): int
    {
        $source = $this->find($sourceId);

        if (!$source) {
            return 0;
        }

        // Fields to copy
        $fieldsToCopy = [
            'prescription_type',
            'od_sphere', 'od_cylinder', 'od_axis', 'od_add', 'od_prism', 'od_base', 'od_pd',
            'os_sphere', 'os_cylinder', 'os_axis', 'os_add', 'os_prism', 'os_base', 'os_pd',
            'od_bc', 'od_diameter', 'od_brand',
            'os_bc', 'os_diameter', 'os_brand'
        ];

        $data = [
            'patient_id' => $patientId,
            'provider_id' => $providerId,
            'examination_id' => $examinationId,
            'prescription_date' => date('Y-m-d'),
            'expiration_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active'
        ];

        foreach ($fieldsToCopy as $field) {
            if (isset($source[$field])) {
                $data[$field] = $source[$field];
            }
        }

        return $this->create($data);
    }

    /**
     * Get prescriptions by provider ID
     *
     * @param int $providerId Provider ID
     * @param string|null $date Date filter (YYYY-MM-DD)
     * @return array
     */
    public function getByProviderId(int $providerId, ?string $date = null): array
    {
        $sql = "SELECT p.*,
                CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                pt.patient_number
                FROM {$this->table} p
                LEFT JOIN patients pt ON p.patient_id = pt.id
                WHERE p.provider_id = ? AND p.{$this->deletedAt} IS NULL";

        $params = [$providerId];

        if ($date) {
            $sql .= " AND p.prescription_date = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY p.prescription_date DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get prescription statistics
     *
     * @param int|null $providerId Provider ID filter
     * @param string|null $startDate Start date filter
     * @param string|null $endDate End date filter
     * @return array
     */
    public function getStatistics(?int $providerId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = [];
        $params = [];

        if ($providerId) {
            $where[] = "provider_id = ?";
            $params[] = $providerId;
        }

        if ($startDate && $endDate) {
            $where[] = "prescription_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : null;

        $total = $this->count($whereClause, $params);

        $statusCounts = $this->db->select(
            "SELECT status, COUNT(*) as count FROM {$this->table}
             WHERE " . ($whereClause ? $whereClause . " AND " : "") . "{$this->deletedAt} IS NULL
             GROUP BY status",
            $params
        );

        $typeCounts = $this->db->select(
            "SELECT prescription_type, COUNT(*) as count FROM {$this->table}
             WHERE " . ($whereClause ? $whereClause . " AND " : "") . "{$this->deletedAt} IS NULL
             GROUP BY prescription_type",
            $params
        );

        return [
            'total' => $total,
            'by_status' => $statusCounts,
            'by_type' => $typeCounts
        ];
    }

    /**
     * Search prescriptions
     *
     * @param string $term Search term
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function search(string $term, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                CONCAT(pt.first_name, ' ', pt.last_name) as patient_name,
                pt.patient_number,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name
                FROM {$this->table} p
                LEFT JOIN patients pt ON p.patient_id = pt.id
                LEFT JOIN users u ON p.provider_id = u.id
                WHERE (pt.first_name LIKE ? OR pt.last_name LIKE ? OR pt.patient_number LIKE ?)
                AND p.{$this->deletedAt} IS NULL
                ORDER BY p.prescription_date DESC
                LIMIT ? OFFSET ?";

        $searchTerm = "%{$term}%";
        return $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    }
}
