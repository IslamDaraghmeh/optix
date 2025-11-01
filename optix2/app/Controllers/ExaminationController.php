<?php
/**
 * Examination Controller
 *
 * Handles eye examination operations including create, edit, view, compare, and image upload
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Models\Examination;
use App\Models\Patient;
use App\Helpers\FileUpload;

class ExaminationController extends BaseController
{
    /**
     * @var Examination Examination model
     */
    private Examination $examinationModel;

    /**
     * @var Patient Patient model
     */
    private Patient $patientModel;

    /**
     * @var FileUpload File upload helper
     */
    private FileUpload $fileUpload;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->examinationModel = new Examination();
        $this->patientModel = new Patient();
        $this->fileUpload = new FileUpload();
    }

    /**
     * List examinations
     *
     * @return void
     */
    public function index(): void
    {
        $this->requirePermission('view_examinations');

        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $search = $this->get('search', '');
        $providerId = $this->get('provider_id', null);
        $startDate = $this->get('start_date', null);
        $endDate = $this->get('end_date', null);

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(p.first_name LIKE ? OR p.last_name LIKE ? OR p.patient_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($providerId) {
            $where[] = "e.provider_id = ?";
            $params[] = $providerId;
        }

        if ($startDate && $endDate) {
            $where[] = "e.exam_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : "1=1";

        $sql = "SELECT e.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                p.patient_number,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                l.name as location_name
                FROM examinations e
                LEFT JOIN patients p ON e.patient_id = p.id
                LEFT JOIN users u ON e.provider_id = u.id
                LEFT JOIN locations l ON e.location_id = l.id
                WHERE {$whereClause} AND e.deleted_at IS NULL";

        $examinations = $this->examinationModel->paginate($page, $perPage, $whereClause, $params, 'exam_date', 'DESC');

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $examinations]);
        } else {
            $this->view('examinations/index', [
                'examinations' => $examinations,
                'filters' => [
                    'search' => $search,
                    'provider_id' => $providerId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
        }
    }

    /**
     * View examination details
     *
     * @param int $id Examination ID
     * @return void
     */
    public function view(int $id): void
    {
        $this->requirePermission('view_examinations');

        $examination = $this->examinationModel->getWithRelations($id);

        if (!$examination) {
            $this->error404('Examination not found');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $examination]);
        } else {
            $this->view('examinations/view', ['examination' => $examination]);
        }
    }

    /**
     * Create new examination form
     *
     * @param int $patientId Patient ID
     * @return void
     */
    public function create(int $patientId): void
    {
        $this->requirePermission('create_examinations');

        $patient = $this->patientModel->find($patientId);

        if (!$patient) {
            $this->error404('Patient not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = [
                'exam_date' => 'required|date',
                'exam_time' => 'required',
                'provider_id' => 'required|integer',
                'location_id' => 'required|integer'
            ];

            if (!$this->validate($this->post(), $rules)) {
                $this->flashAndRedirect('error', 'Validation failed', $this->back());
            }

            $data = [
                'patient_id' => $patientId,
                'provider_id' => $this->post('provider_id'),
                'location_id' => $this->post('location_id'),
                'exam_date' => $this->post('exam_date'),
                'exam_time' => $this->post('exam_time'),
                'chief_complaint' => $this->post('chief_complaint'),
                'history_of_present_illness' => $this->post('history_of_present_illness'),
                'medical_history' => $this->post('medical_history'),
                'ocular_history' => $this->post('ocular_history'),
                'current_medications' => $this->post('current_medications'),
                'allergies' => $this->post('allergies'),

                // Visual Acuity
                'va_od_distance' => $this->post('va_od_distance'),
                'va_os_distance' => $this->post('va_os_distance'),
                'va_ou_distance' => $this->post('va_ou_distance'),
                'va_od_near' => $this->post('va_od_near'),
                'va_os_near' => $this->post('va_os_near'),
                'va_ou_near' => $this->post('va_ou_near'),

                // Refraction
                'od_sphere' => $this->post('od_sphere'),
                'od_cylinder' => $this->post('od_cylinder'),
                'od_axis' => $this->post('od_axis'),
                'od_add' => $this->post('od_add'),
                'od_prism' => $this->post('od_prism'),
                'od_va' => $this->post('od_va'),
                'os_sphere' => $this->post('os_sphere'),
                'os_cylinder' => $this->post('os_cylinder'),
                'os_axis' => $this->post('os_axis'),
                'os_add' => $this->post('os_add'),
                'os_prism' => $this->post('os_prism'),
                'os_va' => $this->post('os_va'),

                // Keratometry
                'k_od_flat' => $this->post('k_od_flat'),
                'k_od_steep' => $this->post('k_od_steep'),
                'k_od_axis' => $this->post('k_od_axis'),
                'k_os_flat' => $this->post('k_os_flat'),
                'k_os_steep' => $this->post('k_os_steep'),
                'k_os_axis' => $this->post('k_os_axis'),

                // IOP
                'iop_od' => $this->post('iop_od'),
                'iop_os' => $this->post('iop_os'),
                'iop_method' => $this->post('iop_method'),

                // Pupils
                'pupils_od' => $this->post('pupils_od'),
                'pupils_os' => $this->post('pupils_os'),

                // EOM
                'eom_od' => $this->post('eom_od'),
                'eom_os' => $this->post('eom_os'),

                // Confrontation Fields
                'cf_od' => $this->post('cf_od'),
                'cf_os' => $this->post('cf_os'),

                // Anterior Segment
                'lids_lashes_od' => $this->post('lids_lashes_od'),
                'lids_lashes_os' => $this->post('lids_lashes_os'),
                'conjunctiva_sclera_od' => $this->post('conjunctiva_sclera_od'),
                'conjunctiva_sclera_os' => $this->post('conjunctiva_sclera_os'),
                'cornea_od' => $this->post('cornea_od'),
                'cornea_os' => $this->post('cornea_os'),
                'anterior_chamber_od' => $this->post('anterior_chamber_od'),
                'anterior_chamber_os' => $this->post('anterior_chamber_os'),
                'iris_od' => $this->post('iris_od'),
                'iris_os' => $this->post('iris_os'),
                'lens_od' => $this->post('lens_od'),
                'lens_os' => $this->post('lens_os'),

                // Posterior Segment
                'vitreous_od' => $this->post('vitreous_od'),
                'vitreous_os' => $this->post('vitreous_os'),
                'disc_od' => $this->post('disc_od'),
                'disc_os' => $this->post('disc_os'),
                'cup_disc_ratio_od' => $this->post('cup_disc_ratio_od'),
                'cup_disc_ratio_os' => $this->post('cup_disc_ratio_os'),
                'macula_od' => $this->post('macula_od'),
                'macula_os' => $this->post('macula_os'),
                'vessels_od' => $this->post('vessels_od'),
                'vessels_os' => $this->post('vessels_os'),
                'periphery_od' => $this->post('periphery_od'),
                'periphery_os' => $this->post('periphery_os'),

                // Assessment & Plan
                'assessment' => $this->post('assessment'),
                'plan' => $this->post('plan'),

                'status' => $this->post('status', 'draft')
            ];

            try {
                $id = $this->examinationModel->create($data);
                $this->logActivity('examination_created', "Created examination ID: {$id} for patient: {$patient['patient_number']}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Examination created successfully', 'id' => $id]);
                } else {
                    $this->flashAndRedirect('success', 'Examination created successfully', APP_URL . '/examinations/view/' . $id);
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to create examination'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to create examination', $this->back());
                }
            }
        } else {
            $this->view('examinations/create', [
                'patient' => $patient,
                'previousExam' => $this->examinationModel->getPreviousExamination($patientId)
            ]);
        }
    }

    /**
     * Edit examination
     *
     * @param int $id Examination ID
     * @return void
     */
    public function edit(int $id): void
    {
        $this->requirePermission('edit_examinations');

        $examination = $this->examinationModel->getWithRelations($id);

        if (!$examination) {
            $this->error404('Examination not found');
        }

        // Cannot edit signed examinations
        if ($examination['status'] === 'signed' && !$this->hasPermission('edit_signed_examinations')) {
            $this->error403('Cannot edit signed examination');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = array_filter([
                'chief_complaint' => $this->post('chief_complaint'),
                'history_of_present_illness' => $this->post('history_of_present_illness'),
                'medical_history' => $this->post('medical_history'),
                'ocular_history' => $this->post('ocular_history'),
                'current_medications' => $this->post('current_medications'),
                'allergies' => $this->post('allergies'),
                'va_od_distance' => $this->post('va_od_distance'),
                'va_os_distance' => $this->post('va_os_distance'),
                'va_ou_distance' => $this->post('va_ou_distance'),
                'va_od_near' => $this->post('va_od_near'),
                'va_os_near' => $this->post('va_os_near'),
                'va_ou_near' => $this->post('va_ou_near'),
                'od_sphere' => $this->post('od_sphere'),
                'od_cylinder' => $this->post('od_cylinder'),
                'od_axis' => $this->post('od_axis'),
                'od_add' => $this->post('od_add'),
                'od_prism' => $this->post('od_prism'),
                'od_va' => $this->post('od_va'),
                'os_sphere' => $this->post('os_sphere'),
                'os_cylinder' => $this->post('os_cylinder'),
                'os_axis' => $this->post('os_axis'),
                'os_add' => $this->post('os_add'),
                'os_prism' => $this->post('os_prism'),
                'os_va' => $this->post('os_va'),
                'k_od_flat' => $this->post('k_od_flat'),
                'k_od_steep' => $this->post('k_od_steep'),
                'k_od_axis' => $this->post('k_od_axis'),
                'k_os_flat' => $this->post('k_os_flat'),
                'k_os_steep' => $this->post('k_os_steep'),
                'k_os_axis' => $this->post('k_os_axis'),
                'iop_od' => $this->post('iop_od'),
                'iop_os' => $this->post('iop_os'),
                'iop_method' => $this->post('iop_method'),
                'pupils_od' => $this->post('pupils_od'),
                'pupils_os' => $this->post('pupils_os'),
                'eom_od' => $this->post('eom_od'),
                'eom_os' => $this->post('eom_os'),
                'cf_od' => $this->post('cf_od'),
                'cf_os' => $this->post('cf_os'),
                'lids_lashes_od' => $this->post('lids_lashes_od'),
                'lids_lashes_os' => $this->post('lids_lashes_os'),
                'conjunctiva_sclera_od' => $this->post('conjunctiva_sclera_od'),
                'conjunctiva_sclera_os' => $this->post('conjunctiva_sclera_os'),
                'cornea_od' => $this->post('cornea_od'),
                'cornea_os' => $this->post('cornea_os'),
                'anterior_chamber_od' => $this->post('anterior_chamber_od'),
                'anterior_chamber_os' => $this->post('anterior_chamber_os'),
                'iris_od' => $this->post('iris_od'),
                'iris_os' => $this->post('iris_os'),
                'lens_od' => $this->post('lens_od'),
                'lens_os' => $this->post('lens_os'),
                'vitreous_od' => $this->post('vitreous_od'),
                'vitreous_os' => $this->post('vitreous_os'),
                'disc_od' => $this->post('disc_od'),
                'disc_os' => $this->post('disc_os'),
                'cup_disc_ratio_od' => $this->post('cup_disc_ratio_od'),
                'cup_disc_ratio_os' => $this->post('cup_disc_ratio_os'),
                'macula_od' => $this->post('macula_od'),
                'macula_os' => $this->post('macula_os'),
                'vessels_od' => $this->post('vessels_od'),
                'vessels_os' => $this->post('vessels_os'),
                'periphery_od' => $this->post('periphery_od'),
                'periphery_os' => $this->post('periphery_os'),
                'assessment' => $this->post('assessment'),
                'plan' => $this->post('plan'),
                'status' => $this->post('status')
            ], function($value) {
                return $value !== null && $value !== '';
            });

            try {
                $this->examinationModel->update($id, $data);
                $this->logActivity('examination_updated', "Updated examination ID: {$id}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Examination updated successfully']);
                } else {
                    $this->flashAndRedirect('success', 'Examination updated successfully', APP_URL . '/examinations/view/' . $id);
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to update examination'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to update examination', $this->back());
                }
            }
        } else {
            $this->view('examinations/edit', ['examination' => $examination]);
        }
    }

    /**
     * Compare two examinations
     *
     * @param int $exam1Id First examination ID
     * @param int $exam2Id Second examination ID
     * @return void
     */
    public function compare(int $exam1Id, int $exam2Id): void
    {
        $this->requirePermission('view_examinations');

        $comparison = $this->examinationModel->compareExaminations($exam1Id, $exam2Id);

        if (empty($comparison)) {
            $this->error404('One or both examinations not found');
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $comparison]);
        } else {
            $this->view('examinations/compare', ['comparison' => $comparison]);
        }
    }

    /**
     * Upload examination image
     *
     * @param int $id Examination ID
     * @return void
     */
    public function uploadImage(int $id): void
    {
        $this->requirePermission('edit_examinations');
        $this->requireCsrfToken();

        $examination = $this->examinationModel->find($id);

        if (!$examination) {
            $this->json(['success' => false, 'message' => 'Examination not found'], 404);
        }

        $field = $this->post('field');
        $allowedFields = ['retinal_image_od', 'retinal_image_os', 'oct_scan_od', 'oct_scan_os'];

        if (!in_array($field, $allowedFields)) {
            $this->json(['success' => false, 'message' => 'Invalid field'], 400);
        }

        if (!isset($_FILES['image'])) {
            $this->json(['success' => false, 'message' => 'No image uploaded'], 400);
        }

        try {
            $uploadPath = STORAGE_PATH . '/examinations';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $result = $this->fileUpload->upload(
                $_FILES['image'],
                $uploadPath,
                ['jpg', 'jpeg', 'png', 'gif'],
                5 * 1024 * 1024, // 5MB
                true // Resize
            );

            if (!$result['success']) {
                $this->json(['success' => false, 'message' => $result['message']], 400);
            }

            // Delete old image if exists
            if ($examination[$field]) {
                $oldFile = $uploadPath . '/' . $examination[$field];
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            $this->examinationModel->saveImage($id, $field, $result['filename']);
            $this->logActivity('examination_image_uploaded', "Uploaded {$field} for examination ID: {$id}");

            $this->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'filename' => $result['filename'],
                'url' => APP_URL . '/storage/examinations/' . $result['filename']
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to upload image'], 500);
        }
    }

    /**
     * Delete examination
     *
     * @param int $id Examination ID
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requirePermission('delete_examinations');
        $this->requireCsrfToken();

        $examination = $this->examinationModel->find($id);

        if (!$examination) {
            $this->json(['success' => false, 'message' => 'Examination not found'], 404);
        }

        // Cannot delete signed examinations
        if ($examination['status'] === 'signed' && !$this->hasPermission('delete_signed_examinations')) {
            $this->json(['success' => false, 'message' => 'Cannot delete signed examination'], 403);
        }

        try {
            $this->examinationModel->delete($id);
            $this->logActivity('examination_deleted', "Deleted examination ID: {$id}");

            $this->json(['success' => true, 'message' => 'Examination deleted successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete examination'], 500);
        }
    }
}
