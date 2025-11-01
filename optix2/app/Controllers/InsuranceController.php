<?php
/**
 * Insurance Controller - Manages insurance verification, claims, and benefits
 */

namespace App\Controllers;

use App\Models\InsuranceProvider;
use App\Models\PatientInsurance;
use App\Models\InsuranceClaim;

class InsuranceController extends BaseController
{
    private InsuranceProvider $providerModel;
    private PatientInsurance $patientInsuranceModel;
    private InsuranceClaim $claimModel;

    public function __construct()
    {
        parent::__construct();
        $this->providerModel = new InsuranceProvider();
        $this->patientInsuranceModel = new PatientInsurance();
        $this->claimModel = new InsuranceClaim();
    }

    public function verifyEligibility(int $patientInsuranceId): void
    {
        $this->requirePermission('verify_insurance');

        $result = $this->patientInsuranceModel->checkEligibility($patientInsuranceId);

        $this->json(['success' => true, 'data' => $result]);
    }

    public function calculateBenefits(): void
    {
        $this->requirePermission('calculate_benefits');
        $this->requireCsrfToken();

        $patientInsuranceId = (int)$this->post('patient_insurance_id');
        $serviceAmount = (float)$this->post('service_amount');

        $insurance = $this->patientInsuranceModel->find($patientInsuranceId);

        if (!$insurance) {
            $this->json(['success' => false, 'message' => 'Insurance not found'], 404);
        }

        $copay = $insurance['copay_amount'] ?? 0;
        $insuranceResponsibility = $serviceAmount - $copay;

        $this->json([
            'success' => true,
            'data' => [
                'total_amount' => $serviceAmount,
                'copay' => $copay,
                'insurance_responsibility' => max(0, $insuranceResponsibility),
                'patient_responsibility' => $copay
            ]
        ]);
    }

    public function submitClaim(): void
    {
        $this->requirePermission('submit_claims');
        $this->requireCsrfToken();

        $rules = [
            'patient_id' => 'required|integer',
            'patient_insurance_id' => 'required|integer',
            'service_date' => 'required|date',
            'billed_amount' => 'required'
        ];

        if (!$this->validate($this->post(), $rules)) {
            $this->json(['success' => false, 'errors' => $this->getValidationErrors()], 400);
        }

        $data = [
            'claim_number' => $this->claimModel->generateClaimNumber(),
            'patient_id' => $this->post('patient_id'),
            'patient_insurance_id' => $this->post('patient_insurance_id'),
            'transaction_id' => $this->post('transaction_id'),
            'examination_id' => $this->post('examination_id'),
            'claim_date' => date('Y-m-d'),
            'service_date' => $this->post('service_date'),
            'provider_id' => $this->post('provider_id'),
            'location_id' => $this->post('location_id'),
            'diagnosis_codes' => $this->post('diagnosis_codes'),
            'procedure_codes' => $this->post('procedure_codes'),
            'billed_amount' => $this->post('billed_amount'),
            'status' => 'draft'
        ];

        try {
            $id = $this->claimModel->create($data);
            $this->logActivity('claim_created', "Created insurance claim ID: {$id}");

            $this->json(['success' => true, 'message' => 'Claim created successfully', 'id' => $id]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create claim'], 500);
        }
    }

    public function claimStatus(int $id): void
    {
        $this->requirePermission('view_claims');

        $claim = $this->claimModel->find($id);

        if (!$claim) {
            $this->json(['success' => false, 'message' => 'Claim not found'], 404);
        }

        $this->json(['success' => true, 'data' => $claim]);
    }

    public function claimsList(): void
    {
        $this->requirePermission('view_claims');

        $page = (int)$this->get('page', 1);
        $status = $this->get('status', null);
        $locationId = $this->get('location_id', null);

        $where = [];
        $params = [];

        if ($status) {
            $where[] = "status = ?";
            $params[] = $status;
        }

        if ($locationId) {
            $where[] = "location_id = ?";
            $params[] = $locationId;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : null;

        $claims = $this->claimModel->paginate($page, 20, $whereClause, $params, 'claim_date', 'DESC');

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $claims]);
        } else {
            $this->view('insurance/claims', ['claims' => $claims, 'status' => $status]);
        }
    }

    public function updateClaimStatus(int $id): void
    {
        $this->requirePermission('edit_claims');
        $this->requireCsrfToken();

        $status = $this->post('status');
        $additionalData = [];

        if ($status === 'paid') {
            $additionalData['insurance_paid'] = $this->post('insurance_paid');
            $additionalData['patient_responsibility'] = $this->post('patient_responsibility');
        } elseif ($status === 'rejected') {
            $additionalData['rejection_reason'] = $this->post('rejection_reason');
        }

        try {
            $result = $this->claimModel->updateClaimStatus($id, $status, $additionalData);

            if ($result) {
                $this->logActivity('claim_status_updated', "Updated claim ID: {$id} to status: {$status}");
                $this->json(['success' => true, 'message' => 'Claim status updated successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to update claim'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update claim'], 500);
        }
    }
}
