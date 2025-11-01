<?php
/**
 * Patient Model
 *
 * Handles patient data operations
 *
 * @package App\Models
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Models;

class Patient extends BaseModel
{
    /**
     * Table name
     */
    protected string $table = 'patients';

    /**
     * Generate unique patient number
     *
     * @return string
     */
    public function generatePatientNumber(): string
    {
        $prefix = 'P';
        $year = date('Y');
        $lastPatient = $this->db->selectOne(
            "SELECT patient_number FROM {$this->table}
             WHERE patient_number LIKE '{$prefix}{$year}%'
             ORDER BY id DESC LIMIT 1"
        );

        if ($lastPatient) {
            $lastNumber = (int)substr($lastPatient['patient_number'], -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Search patients
     *
     * @param string $term Search term
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function search(string $term, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR patient_number LIKE ?)
                AND {$this->deletedAt} IS NULL
                ORDER BY last_name, first_name
                LIMIT ? OFFSET ?";

        $searchTerm = "%{$term}%";
        return $this->db->select($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
    }

    /**
     * Get patient with history
     *
     * @param int $id Patient ID
     * @return array|false
     */
    public function getWithHistory(int $id)
    {
        $patient = $this->find($id);
        if (!$patient) {
            return false;
        }

        // Get examinations
        $patient['examinations'] = $this->db->select(
            "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as provider_name
             FROM examinations e
             LEFT JOIN users u ON e.provider_id = u.id
             WHERE e.patient_id = ? AND e.deleted_at IS NULL
             ORDER BY e.exam_date DESC, e.exam_time DESC",
            [$id]
        );

        // Get prescriptions
        $patient['prescriptions'] = $this->db->select(
            "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as provider_name
             FROM prescriptions p
             LEFT JOIN users u ON p.provider_id = u.id
             WHERE p.patient_id = ? AND p.deleted_at IS NULL
             ORDER BY p.prescription_date DESC",
            [$id]
        );

        // Get appointments
        $patient['appointments'] = $this->db->select(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as provider_name
             FROM appointments a
             LEFT JOIN users u ON a.provider_id = u.id
             WHERE a.patient_id = ? AND a.deleted_at IS NULL
             ORDER BY a.appointment_date DESC, a.appointment_time DESC
             LIMIT 10",
            [$id]
        );

        // Get transactions
        $patient['transactions'] = $this->db->select(
            "SELECT * FROM transactions
             WHERE patient_id = ? AND deleted_at IS NULL
             ORDER BY transaction_date DESC, transaction_time DESC
             LIMIT 10",
            [$id]
        );

        return $patient;
    }

    /**
     * Get patient statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $total = $this->count();
        $active = $this->count("status = 'active'");
        $inactive = $this->count("status = 'inactive'");

        $newThisMonth = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM {$this->table}
             WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())
             AND {$this->deletedAt} IS NULL"
        )['count'];

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'new_this_month' => $newThisMonth,
        ];
    }
}
