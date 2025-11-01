<?php
/**
 * Prescription Controller
 *
 * Handles prescription operations including create, edit, view, print, email, and copy
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Examination;
use App\Helpers\PDF;
use App\Helpers\Email;

class PrescriptionController extends BaseController
{
    /**
     * @var Prescription Prescription model
     */
    private Prescription $prescriptionModel;

    /**
     * @var Patient Patient model
     */
    private Patient $patientModel;

    /**
     * @var Examination Examination model
     */
    private Examination $examinationModel;

    /**
     * @var PDF PDF helper
     */
    private PDF $pdf;

    /**
     * @var Email Email helper
     */
    private Email $email;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->prescriptionModel = new Prescription();
        $this->patientModel = new Patient();
        $this->examinationModel = new Examination();
        $this->pdf = new PDF();
        $this->email = new Email();
    }

    /**
     * List prescriptions
     *
     * @return void
     */
    public function index(): void
    {
        $this->requirePermission('view_prescriptions');

        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $search = $this->get('search', '');
        $status = $this->get('status', null);
        $type = $this->get('type', null);

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(pt.first_name LIKE ? OR pt.last_name LIKE ? OR pt.patient_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($status) {
            $where[] = "p.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $where[] = "p.prescription_type = ?";
            $params[] = $type;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : "1=1";

        $prescriptions = $this->prescriptionModel->paginate($page, $perPage, $whereClause, $params, 'prescription_date', 'DESC');

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $prescriptions]);
        } else {
            $this->view('prescriptions/index', [
                'prescriptions' => $prescriptions,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type
                ]
            ]);
        }
    }

    /**
     * View prescription details
     *
     * @param int $id Prescription ID
     * @return void
     */
    public function view(int $id): void
    {
        $this->requirePermission('view_prescriptions');

        $prescription = $this->prescriptionModel->getWithRelations($id);

        if (!$prescription) {
            $this->error404('Prescription not found');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $prescription]);
        } else {
            $this->view('prescriptions/view', ['prescription' => $prescription]);
        }
    }

    /**
     * Create new prescription
     *
     * @param int $patientId Patient ID
     * @param int|null $examinationId Examination ID (optional)
     * @return void
     */
    public function create(int $patientId, ?int $examinationId = null): void
    {
        $this->requirePermission('create_prescriptions');

        $patient = $this->patientModel->find($patientId);

        if (!$patient) {
            $this->error404('Patient not found');
        }

        $examination = null;
        if ($examinationId) {
            $examination = $this->examinationModel->find($examinationId);
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = [
                'prescription_type' => 'required',
                'prescription_date' => 'required|date',
                'provider_id' => 'required|integer'
            ];

            if (!$this->validate($this->post(), $rules)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'errors' => $this->getValidationErrors()], 400);
                } else {
                    $this->flashAndRedirect('error', 'Validation failed', $this->back());
                }
            }

            $data = [
                'patient_id' => $patientId,
                'examination_id' => $examinationId,
                'provider_id' => $this->post('provider_id'),
                'prescription_type' => $this->post('prescription_type'),
                'prescription_date' => $this->post('prescription_date'),
                'expiration_date' => $this->post('expiration_date'),

                // Right Eye (OD)
                'od_sphere' => $this->post('od_sphere'),
                'od_cylinder' => $this->post('od_cylinder'),
                'od_axis' => $this->post('od_axis'),
                'od_add' => $this->post('od_add'),
                'od_prism' => $this->post('od_prism'),
                'od_base' => $this->post('od_base'),
                'od_pd' => $this->post('od_pd'),

                // Left Eye (OS)
                'os_sphere' => $this->post('os_sphere'),
                'os_cylinder' => $this->post('os_cylinder'),
                'os_axis' => $this->post('os_axis'),
                'os_add' => $this->post('os_add'),
                'os_prism' => $this->post('os_prism'),
                'os_base' => $this->post('os_base'),
                'os_pd' => $this->post('os_pd'),

                // Contact Lens Specific
                'od_bc' => $this->post('od_bc'),
                'od_diameter' => $this->post('od_diameter'),
                'od_brand' => $this->post('od_brand'),
                'os_bc' => $this->post('os_bc'),
                'os_diameter' => $this->post('os_diameter'),
                'os_brand' => $this->post('os_brand'),

                'notes' => $this->post('notes'),
                'status' => 'active'
            ];

            try {
                $id = $this->prescriptionModel->create($data);
                $this->logActivity('prescription_created', "Created prescription ID: {$id} for patient: {$patient['patient_number']}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Prescription created successfully', 'id' => $id]);
                } else {
                    $this->flashAndRedirect('success', 'Prescription created successfully', APP_URL . '/prescriptions/view/' . $id);
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to create prescription'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to create prescription', $this->back());
                }
            }
        } else {
            // Pre-fill from examination if available
            $prefillData = null;
            if ($examination) {
                $prefillData = [
                    'od_sphere' => $examination['od_sphere'],
                    'od_cylinder' => $examination['od_cylinder'],
                    'od_axis' => $examination['od_axis'],
                    'od_add' => $examination['od_add'],
                    'os_sphere' => $examination['os_sphere'],
                    'os_cylinder' => $examination['os_cylinder'],
                    'os_axis' => $examination['os_axis'],
                    'os_add' => $examination['os_add']
                ];
            }

            $this->view('prescriptions/create', [
                'patient' => $patient,
                'examination' => $examination,
                'prefillData' => $prefillData,
                'previousPrescription' => $this->prescriptionModel->getLatestByPatientAndType($patientId)
            ]);
        }
    }

    /**
     * Edit prescription
     *
     * @param int $id Prescription ID
     * @return void
     */
    public function edit(int $id): void
    {
        $this->requirePermission('edit_prescriptions');

        $prescription = $this->prescriptionModel->getWithRelations($id);

        if (!$prescription) {
            $this->error404('Prescription not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = array_filter([
                'prescription_type' => $this->post('prescription_type'),
                'prescription_date' => $this->post('prescription_date'),
                'expiration_date' => $this->post('expiration_date'),
                'od_sphere' => $this->post('od_sphere'),
                'od_cylinder' => $this->post('od_cylinder'),
                'od_axis' => $this->post('od_axis'),
                'od_add' => $this->post('od_add'),
                'od_prism' => $this->post('od_prism'),
                'od_base' => $this->post('od_base'),
                'od_pd' => $this->post('od_pd'),
                'os_sphere' => $this->post('os_sphere'),
                'os_cylinder' => $this->post('os_cylinder'),
                'os_axis' => $this->post('os_axis'),
                'os_add' => $this->post('os_add'),
                'os_prism' => $this->post('os_prism'),
                'os_base' => $this->post('os_base'),
                'os_pd' => $this->post('os_pd'),
                'od_bc' => $this->post('od_bc'),
                'od_diameter' => $this->post('od_diameter'),
                'od_brand' => $this->post('od_brand'),
                'os_bc' => $this->post('os_bc'),
                'os_diameter' => $this->post('os_diameter'),
                'os_brand' => $this->post('os_brand'),
                'notes' => $this->post('notes'),
                'status' => $this->post('status')
            ], function($value) {
                return $value !== null && $value !== '';
            });

            try {
                $this->prescriptionModel->update($id, $data);
                $this->logActivity('prescription_updated', "Updated prescription ID: {$id}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Prescription updated successfully']);
                } else {
                    $this->flashAndRedirect('success', 'Prescription updated successfully', APP_URL . '/prescriptions/view/' . $id);
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to update prescription'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to update prescription', $this->back());
                }
            }
        } else {
            $this->view('prescriptions/edit', ['prescription' => $prescription]);
        }
    }

    /**
     * Print prescription as PDF
     *
     * @param int $id Prescription ID
     * @return void
     */
    public function print(int $id): void
    {
        $this->requirePermission('view_prescriptions');

        $prescription = $this->prescriptionModel->getWithRelations($id);

        if (!$prescription) {
            $this->error404('Prescription not found');
        }

        $html = $this->generatePrescriptionHtml($prescription);

        try {
            $filename = "prescription_{$prescription['patient_number']}_{$prescription['prescription_date']}.pdf";
            $this->pdf->generateFromHtml($html, $filename, 'I'); // I = inline display

            $this->logActivity('prescription_printed', "Printed prescription ID: {$id}");
        } catch (\Exception $e) {
            $this->error500('Failed to generate PDF');
        }
    }

    /**
     * Email prescription to patient
     *
     * @param int $id Prescription ID
     * @return void
     */
    public function email(int $id): void
    {
        $this->requirePermission('email_prescriptions');
        $this->requireCsrfToken();

        $prescription = $this->prescriptionModel->getWithRelations($id);

        if (!$prescription) {
            $this->json(['success' => false, 'message' => 'Prescription not found'], 404);
        }

        if (!$prescription['email']) {
            $this->json(['success' => false, 'message' => 'Patient email not found'], 400);
        }

        try {
            // Generate PDF
            $html = $this->generatePrescriptionHtml($prescription);
            $filename = "prescription_{$prescription['patient_number']}_{$prescription['prescription_date']}.pdf";
            $pdfPath = STORAGE_PATH . '/temp/' . $filename;

            if (!is_dir(STORAGE_PATH . '/temp')) {
                mkdir(STORAGE_PATH . '/temp', 0755, true);
            }

            $this->pdf->generateFromHtml($html, $pdfPath, 'F'); // F = save to file

            // Send email
            $subject = "Your Prescription from " . APP_NAME;
            $body = "Dear {$prescription['patient_name']},<br><br>";
            $body .= "Please find your prescription attached.<br><br>";
            $body .= "Prescription Date: {$prescription['prescription_date']}<br>";
            $body .= "Provider: {$prescription['provider_name']}<br><br>";
            $body .= "If you have any questions, please contact us.<br><br>";
            $body .= "Best regards,<br>" . APP_NAME;

            $sent = $this->email->send(
                $prescription['email'],
                $subject,
                $body,
                [$pdfPath]
            );

            // Clean up temp file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            if ($sent) {
                $this->logActivity('prescription_emailed', "Emailed prescription ID: {$id} to {$prescription['email']}");
                $this->json(['success' => true, 'message' => 'Prescription emailed successfully']);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to send email'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to email prescription'], 500);
        }
    }

    /**
     * Copy from previous prescription
     *
     * @param int $patientId Patient ID
     * @param int $sourceId Source prescription ID
     * @return void
     */
    public function copyFromPrevious(int $patientId, int $sourceId): void
    {
        $this->requirePermission('create_prescriptions');
        $this->requireCsrfToken();

        $patient = $this->patientModel->find($patientId);

        if (!$patient) {
            $this->json(['success' => false, 'message' => 'Patient not found'], 404);
        }

        $source = $this->prescriptionModel->find($sourceId);

        if (!$source || $source['patient_id'] != $patientId) {
            $this->json(['success' => false, 'message' => 'Source prescription not found'], 404);
        }

        try {
            $currentUser = $this->getCurrentUser();
            $newId = $this->prescriptionModel->copyFromPrevious($sourceId, $patientId, $currentUser['id']);

            if ($newId) {
                $this->logActivity('prescription_copied', "Copied prescription from ID: {$sourceId} to new ID: {$newId}");
                $this->json([
                    'success' => true,
                    'message' => 'Prescription copied successfully',
                    'id' => $newId
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Failed to copy prescription'], 500);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to copy prescription'], 500);
        }
    }

    /**
     * Delete prescription
     *
     * @param int $id Prescription ID
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requirePermission('delete_prescriptions');
        $this->requireCsrfToken();

        $prescription = $this->prescriptionModel->find($id);

        if (!$prescription) {
            $this->json(['success' => false, 'message' => 'Prescription not found'], 404);
        }

        try {
            $this->prescriptionModel->delete($id);
            $this->logActivity('prescription_deleted', "Deleted prescription ID: {$id}");

            $this->json(['success' => true, 'message' => 'Prescription deleted successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete prescription'], 500);
        }
    }

    /**
     * Generate prescription HTML for PDF
     *
     * @param array $prescription Prescription data
     * @return string HTML content
     */
    private function generatePrescriptionHtml(array $prescription): string
    {
        $html = '<html><head><style>
            body { font-family: Arial, sans-serif; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { margin: 0; }
            .section { margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 50px; border-top: 1px solid #000; padding-top: 10px; }
        </style></head><body>';

        $html .= '<div class="header">';
        $html .= '<h1>' . APP_NAME . '</h1>';
        $html .= '<p>Prescription</p>';
        $html .= '</div>';

        $html .= '<div class="section">';
        $html .= '<strong>Patient:</strong> ' . $prescription['patient_name'] . '<br>';
        $html .= '<strong>Patient Number:</strong> ' . $prescription['patient_number'] . '<br>';
        $html .= '<strong>Date of Birth:</strong> ' . $prescription['date_of_birth'] . '<br>';
        $html .= '<strong>Prescription Date:</strong> ' . $prescription['prescription_date'] . '<br>';
        if ($prescription['expiration_date']) {
            $html .= '<strong>Expiration Date:</strong> ' . $prescription['expiration_date'] . '<br>';
        }
        $html .= '<strong>Type:</strong> ' . ucfirst(str_replace('_', ' ', $prescription['prescription_type'])) . '<br>';
        $html .= '</div>';

        $html .= '<table>';
        $html .= '<tr><th>Eye</th><th>Sphere</th><th>Cylinder</th><th>Axis</th><th>Add</th><th>Prism</th><th>Base</th><th>PD</th></tr>';
        $html .= '<tr>';
        $html .= '<td><strong>OD (Right)</strong></td>';
        $html .= '<td>' . ($prescription['od_sphere'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_cylinder'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_axis'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_add'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_prism'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_base'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['od_pd'] ?? '-') . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td><strong>OS (Left)</strong></td>';
        $html .= '<td>' . ($prescription['os_sphere'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_cylinder'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_axis'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_add'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_prism'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_base'] ?? '-') . '</td>';
        $html .= '<td>' . ($prescription['os_pd'] ?? '-') . '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        if ($prescription['prescription_type'] === 'contact_lenses' && ($prescription['od_bc'] || $prescription['os_bc'])) {
            $html .= '<div class="section">';
            $html .= '<h3>Contact Lens Parameters</h3>';
            $html .= '<table>';
            $html .= '<tr><th>Eye</th><th>Base Curve</th><th>Diameter</th><th>Brand</th></tr>';
            $html .= '<tr><td><strong>OD (Right)</strong></td><td>' . ($prescription['od_bc'] ?? '-') . '</td><td>' . ($prescription['od_diameter'] ?? '-') . '</td><td>' . ($prescription['od_brand'] ?? '-') . '</td></tr>';
            $html .= '<tr><td><strong>OS (Left)</strong></td><td>' . ($prescription['os_bc'] ?? '-') . '</td><td>' . ($prescription['os_diameter'] ?? '-') . '</td><td>' . ($prescription['os_brand'] ?? '-') . '</td></tr>';
            $html .= '</table>';
            $html .= '</div>';
        }

        if ($prescription['notes']) {
            $html .= '<div class="section">';
            $html .= '<strong>Notes:</strong><br>' . nl2br(htmlspecialchars($prescription['notes']));
            $html .= '</div>';
        }

        $html .= '<div class="footer">';
        $html .= '<p><strong>Provider:</strong> ' . $prescription['provider_name'] . '</p>';
        $html .= '</div>';

        $html .= '</body></html>';

        return $html;
    }
}
