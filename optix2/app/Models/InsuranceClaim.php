<?php
/**
 * Insurance Claim Model
 */

namespace App\Models;

class InsuranceClaim extends BaseModel
{
    protected string $table = 'insurance_claims';

    public function generateClaimNumber(): string
    {
        $prefix = 'CLM';
        $date = date('Ymd');
        $last = $this->db->selectOne(
            "SELECT claim_number FROM {$this->table} WHERE claim_number LIKE '{$prefix}{$date}%' ORDER BY id DESC LIMIT 1"
        );

        $num = $last ? (int)substr($last['claim_number'], -4) + 1 : 1;
        return $prefix . $date . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function getByPatientId(int $patientId): array
    {
        $sql = "SELECT ic.*, ip.name as provider_name, pi.policy_number
                FROM {$this->table} ic
                LEFT JOIN patient_insurance pi ON ic.patient_insurance_id = pi.id
                LEFT JOIN insurance_providers ip ON pi.provider_id = ip.id
                WHERE ic.patient_id = ? AND ic.{$this->deletedAt} IS NULL
                ORDER BY ic.claim_date DESC";

        return $this->db->select($sql, [$patientId]);
    }

    public function getPendingClaims(?int $locationId = null): array
    {
        $sql = "SELECT ic.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                ip.name as provider_name
                FROM {$this->table} ic
                LEFT JOIN patients p ON ic.patient_id = p.id
                LEFT JOIN patient_insurance pi ON ic.patient_insurance_id = pi.id
                LEFT JOIN insurance_providers ip ON pi.provider_id = ip.id
                WHERE ic.status IN ('draft', 'submitted', 'pending')
                AND ic.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND ic.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY ic.claim_date ASC";

        return $this->db->select($sql, $params);
    }

    public function submitClaim(int $id, int $userId): bool
    {
        return $this->update($id, [
            'status' => 'submitted',
            'submitted_at' => date(DATETIME_FORMAT),
            'submitted_by' => $userId
        ]) > 0;
    }

    public function updateClaimStatus(int $id, string $status, ?array $additionalData = []): bool
    {
        $data = array_merge(['status' => $status], $additionalData);

        if ($status === 'paid') {
            $data['paid_at'] = date(DATETIME_FORMAT);
        } elseif ($status === 'approved') {
            $data['processed_at'] = date(DATETIME_FORMAT);
        }

        return $this->update($id, $data) > 0;
    }

    public function getStatistics(?int $locationId = null): array
    {
        $where = $locationId ? "location_id = {$locationId}" : null;

        $statusCounts = $this->db->select(
            "SELECT status, COUNT(*) as count, SUM(billed_amount) as total_billed
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
