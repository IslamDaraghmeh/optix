<?php
/**
 * Examination Model
 *
 * Handles eye examination data operations including CRUD, comparison, and image handling
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Examination extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'examinations';

    /**
     * Get examinations by patient ID
     *
     * @param int $patientId Patient ID
     * @param int|null $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getByPatientId(int $patientId, ?int $limit = null, int $offset = 0): array
    {
        $sql = "SELECT e.*,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                l.name as location_name
                FROM {$this->table} e
                LEFT JOIN users u ON e.provider_id = u.id
                LEFT JOIN locations l ON e.location_id = l.id
                WHERE e.patient_id = ? AND e.{$this->deletedAt} IS NULL
                ORDER BY e.exam_date DESC, e.exam_time DESC";

        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }

        return $this->db->select($sql, [$patientId]);
    }

    /**
     * Get examinations by provider ID
     *
     * @param int $providerId Provider ID
     * @param string|null $date Date filter (YYYY-MM-DD)
     * @return array
     */
    public function getByProviderId(int $providerId, ?string $date = null): array
    {
        $sql = "SELECT e.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_number,
                l.name as location_name
                FROM {$this->table} e
                LEFT JOIN patients p ON e.patient_id = p.id
                LEFT JOIN locations l ON e.location_id = l.id
                WHERE e.provider_id = ? AND e.{$this->deletedAt} IS NULL";

        $params = [$providerId];

        if ($date) {
            $sql .= " AND e.exam_date = ?";
            $params[] = $date;
        }

        $sql .= " ORDER BY e.exam_date DESC, e.exam_time DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get examination with related data
     *
     * @param int $id Examination ID
     * @return array|false
     */
    public function getWithRelations(int $id)
    {
        $sql = "SELECT e.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_number,
                p.date_of_birth,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                u.role as provider_role,
                l.name as location_name,
                l.address as location_address
                FROM {$this->table} e
                LEFT JOIN patients p ON e.patient_id = p.id
                LEFT JOIN users u ON e.provider_id = u.id
                LEFT JOIN locations l ON e.location_id = l.id
                WHERE e.id = ? AND e.{$this->deletedAt} IS NULL";

        return $this->db->selectOne($sql, [$id]);
    }

    /**
     * Compare two examinations
     *
     * @param int $exam1Id First examination ID
     * @param int $exam2Id Second examination ID
     * @return array Comparison data
     */
    public function compareExaminations(int $exam1Id, int $exam2Id): array
    {
        $exam1 = $this->getWithRelations($exam1Id);
        $exam2 = $this->getWithRelations($exam2Id);

        if (!$exam1 || !$exam2) {
            return [];
        }

        $fields = [
            'visual_acuity' => ['va_od_distance', 'va_os_distance', 'va_od_near', 'va_os_near'],
            'refraction' => ['od_sphere', 'od_cylinder', 'od_axis', 'od_add', 'os_sphere', 'os_cylinder', 'os_axis', 'os_add'],
            'keratometry' => ['k_od_flat', 'k_od_steep', 'k_od_axis', 'k_os_flat', 'k_os_steep', 'k_os_axis'],
            'iop' => ['iop_od', 'iop_os', 'iop_method'],
            'pupils' => ['pupils_od', 'pupils_os'],
            'eom' => ['eom_od', 'eom_os'],
            'anterior_segment' => ['cornea_od', 'cornea_os', 'lens_od', 'lens_os'],
            'posterior_segment' => ['disc_od', 'disc_os', 'cup_disc_ratio_od', 'cup_disc_ratio_os', 'macula_od', 'macula_os']
        ];

        $comparison = [
            'exam1' => $exam1,
            'exam2' => $exam2,
            'differences' => []
        ];

        foreach ($fields as $category => $fieldList) {
            foreach ($fieldList as $field) {
                if (isset($exam1[$field]) && isset($exam2[$field]) && $exam1[$field] !== $exam2[$field]) {
                    $comparison['differences'][$field] = [
                        'category' => $category,
                        'old_value' => $exam1[$field],
                        'new_value' => $exam2[$field]
                    ];
                }
            }
        }

        return $comparison;
    }

    /**
     * Get previous examination for a patient
     *
     * @param int $patientId Patient ID
     * @param int|null $excludeId Examination ID to exclude
     * @return array|false
     */
    public function getPreviousExamination(int $patientId, ?int $excludeId = null)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE patient_id = ? AND {$this->deletedAt} IS NULL";

        $params = [$patientId];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " ORDER BY exam_date DESC, exam_time DESC LIMIT 1";

        return $this->db->selectOne($sql, $params);
    }

    /**
     * Update examination status
     *
     * @param int $id Examination ID
     * @param string $status New status
     * @return int Number of affected rows
     */
    public function updateStatus(int $id, string $status): int
    {
        $data = ['status' => $status];

        if ($status === 'signed') {
            $data['signed_at'] = date(DATETIME_FORMAT);
        }

        return $this->update($id, $data);
    }

    /**
     * Upload and save examination image
     *
     * @param int $id Examination ID
     * @param string $field Field name (retinal_image_od, retinal_image_os, oct_scan_od, oct_scan_os)
     * @param string $filePath Path to uploaded file
     * @return bool Success status
     */
    public function saveImage(int $id, string $field, string $filePath): bool
    {
        $allowedFields = ['retinal_image_od', 'retinal_image_os', 'oct_scan_od', 'oct_scan_os'];

        if (!in_array($field, $allowedFields)) {
            return false;
        }

        $result = $this->update($id, [$field => $filePath]);
        return $result > 0;
    }

    /**
     * Delete examination image
     *
     * @param int $id Examination ID
     * @param string $field Field name
     * @return bool Success status
     */
    public function deleteImage(int $id, string $field): bool
    {
        $allowedFields = ['retinal_image_od', 'retinal_image_os', 'oct_scan_od', 'oct_scan_os'];

        if (!in_array($field, $allowedFields)) {
            return false;
        }

        $exam = $this->find($id);
        if (!$exam || !$exam[$field]) {
            return false;
        }

        // Delete physical file
        $filePath = STORAGE_PATH . '/examinations/' . $exam[$field];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Update database
        $result = $this->update($id, [$field => null]);
        return $result > 0;
    }

    /**
     * Get examinations by date range
     *
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int|null $locationId Location ID filter
     * @return array
     */
    public function getByDateRange(string $startDate, string $endDate, ?int $locationId = null): array
    {
        $sql = "SELECT e.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                l.name as location_name
                FROM {$this->table} e
                LEFT JOIN patients p ON e.patient_id = p.id
                LEFT JOIN users u ON e.provider_id = u.id
                LEFT JOIN locations l ON e.location_id = l.id
                WHERE e.exam_date BETWEEN ? AND ? AND e.{$this->deletedAt} IS NULL";

        $params = [$startDate, $endDate];

        if ($locationId) {
            $sql .= " AND e.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY e.exam_date DESC, e.exam_time DESC";

        return $this->db->select($sql, $params);
    }

    /**
     * Get examination statistics
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
            $where[] = "exam_date BETWEEN ? AND ?";
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

        return [
            'total' => $total,
            'by_status' => $statusCounts
        ];
    }
}
