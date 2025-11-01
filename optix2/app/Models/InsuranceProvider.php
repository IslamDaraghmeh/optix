<?php
/**
 * Insurance Provider Model
 */

namespace App\Models;

class InsuranceProvider extends BaseModel
{
    protected string $table = 'insurance_providers';

    public function getActive(): array
    {
        return $this->where("is_active = TRUE");
    }

    public function findByPayerId(string $payerId)
    {
        return $this->whereOne("payer_id = ?", [$payerId]);
    }

    public function getWithPatientCount(): array
    {
        $sql = "SELECT ip.*, COUNT(DISTINCT pi.patient_id) as patient_count
                FROM {$this->table} ip
                LEFT JOIN patient_insurance pi ON ip.id = pi.provider_id
                WHERE ip.{$this->deletedAt} IS NULL
                GROUP BY ip.id
                ORDER BY ip.name ASC";

        return $this->db->select($sql);
    }
}
