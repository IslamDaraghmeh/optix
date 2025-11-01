<?php
/**
 * Patient Insurance Model
 */

namespace App\Models;

class PatientInsurance extends BaseModel
{
    protected string $table = 'patient_insurance';

    public function getByPatientId(int $patientId): array
    {
        $sql = "SELECT pi.*, ip.name as provider_name
                FROM {$this->table} pi
                LEFT JOIN insurance_providers ip ON pi.provider_id = ip.id
                WHERE pi.patient_id = ? AND pi.{$this->deletedAt} IS NULL
                ORDER BY pi.is_primary DESC";

        return $this->db->select($sql, [$patientId]);
    }

    public function getPrimaryInsurance(int $patientId)
    {
        $sql = "SELECT pi.*, ip.name as provider_name
                FROM {$this->table} pi
                LEFT JOIN insurance_providers ip ON pi.provider_id = ip.id
                WHERE pi.patient_id = ? AND pi.is_primary = TRUE
                AND pi.{$this->deletedAt} IS NULL
                LIMIT 1";

        return $this->db->selectOne($sql, [$patientId]);
    }

    public function checkEligibility(int $id): array
    {
        $insurance = $this->find($id);

        if (!$insurance) {
            return ['eligible' => false, 'message' => 'Insurance not found'];
        }

        if ($insurance['expiration_date'] && strtotime($insurance['expiration_date']) < time()) {
            return ['eligible' => false, 'message' => 'Insurance expired'];
        }

        return ['eligible' => true, 'copay' => $insurance['copay_amount']];
    }
}
