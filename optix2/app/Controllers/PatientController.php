<?php
/**
 * Patient Controller
 *
 * Handles patient management operations
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Models\Patient;

class PatientController extends BaseController
{
    /**
     * @var Patient Patient model
     */
    private Patient $patientModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->patientModel = new Patient();
    }

    /**
     * List all patients
     *
     * @return void
     */
    public function index(): void
    {
        $this->requirePermission(PERM_VIEW_PATIENTS);

        $page = (int)$this->get('page', 1);
        $search = $this->get('search', '');

        if ($search) {
            $patients = $this->patientModel->search($search, PAGINATION_PER_PAGE, ($page - 1) * PAGINATION_PER_PAGE);
            $total = count($patients);
        } else {
            $result = $this->patientModel->paginate($page, PAGINATION_PER_PAGE, null, [], 'last_name', 'ASC');
            $patients = $result['data'];
            $total = $result['total'];
        }

        $this->view('patients/index', [
            'patients' => $patients,
            'search' => $search,
            'page' => $page,
            'total' => $total,
            'per_page' => PAGINATION_PER_PAGE,
        ]);
    }

    /**
     * View patient details
     *
     * @param int $id Patient ID
     * @return void
     */
    public function show(int $id): void
    {
        $this->requirePermission(PERM_VIEW_PATIENTS);

        $patient = $this->patientModel->getWithHistory($id);

        if (!$patient) {
            $this->error404('Patient not found');
        }

        parent::view('patients/view', ['patient' => $patient]);
    }

    /**
     * Create new patient
     *
     * @return void
     */
    public function create(): void
    {
        $this->requirePermission(PERM_MANAGE_PATIENTS);

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = [
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'date_of_birth' => $this->post('date_of_birth'),
                'gender' => $this->post('gender'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'mobile' => $this->post('mobile'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'zip_code' => $this->post('zip_code'),
                'emergency_contact_name' => $this->post('emergency_contact_name'),
                'emergency_contact_phone' => $this->post('emergency_contact_phone'),
                'notes' => $this->post('notes'),
            ];

            $rules = [
                'first_name' => 'required|alpha|min:2|max:100',
                'last_name' => 'required|alpha|min:2|max:100',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'email' => 'email|unique:patients,email',
                'phone' => 'phone',
            ];

            if (!$this->validate($data, $rules)) {
                $this->session->flashInput();
                $errors = $this->getValidationErrors();
                $this->flashAndRedirect('error', 'Please correct the errors below', APP_URL . '/patient/create');
            }

            // Generate patient number
            $data['patient_number'] = $this->patientModel->generatePatientNumber();

            try {
                $patientId = $this->patientModel->create($data);
                $this->logActivity('patient_created', "Created patient: {$data['first_name']} {$data['last_name']}");
                $this->flashAndRedirect('success', 'Patient created successfully', APP_URL . '/patient/view/' . $patientId);
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to create patient', APP_URL . '/patient/create');
            }
        }

        $this->view('patients/create');
    }

    /**
     * Edit patient
     *
     * @param int $id Patient ID
     * @return void
     */
    public function edit(int $id): void
    {
        $this->requirePermission(PERM_MANAGE_PATIENTS);

        $patient = $this->patientModel->find($id);

        if (!$patient) {
            $this->error404('Patient not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = [
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'date_of_birth' => $this->post('date_of_birth'),
                'gender' => $this->post('gender'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'mobile' => $this->post('mobile'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'zip_code' => $this->post('zip_code'),
                'emergency_contact_name' => $this->post('emergency_contact_name'),
                'emergency_contact_phone' => $this->post('emergency_contact_phone'),
                'notes' => $this->post('notes'),
                'status' => $this->post('status'),
            ];

            $rules = [
                'first_name' => 'required|alpha|min:2|max:100',
                'last_name' => 'required|alpha|min:2|max:100',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'email' => "email|unique:patients,email,{$id}",
                'phone' => 'phone',
                'status' => 'required|in:active,inactive',
            ];

            if (!$this->validate($data, $rules)) {
                $this->session->flashInput();
                $errors = $this->getValidationErrors();
                $this->flashAndRedirect('error', 'Please correct the errors below', APP_URL . '/patient/edit/' . $id);
            }

            try {
                $this->patientModel->update($id, $data);
                $this->logActivity('patient_updated', "Updated patient ID: {$id}");
                $this->flashAndRedirect('success', 'Patient updated successfully', APP_URL . '/patient/view/' . $id);
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to update patient', APP_URL . '/patient/edit/' . $id);
            }
        }

        $this->view('patients/edit', ['patient' => $patient]);
    }

    /**
     * Delete patient (soft delete)
     *
     * @param int $id Patient ID
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requirePermission(PERM_MANAGE_PATIENTS);
        $this->requireCsrfToken();

        try {
            $this->patientModel->delete($id);
            $this->logActivity('patient_deleted', "Deleted patient ID: {$id}");
            $this->flashAndRedirect('success', 'Patient deleted successfully', APP_URL . '/patient');
        } catch (\Exception $e) {
            $this->flashAndRedirect('error', 'Failed to delete patient', APP_URL . '/patient');
        }
    }

    /**
     * AJAX search endpoint
     *
     * @return void
     */
    public function search(): void
    {
        $this->requirePermission(PERM_VIEW_PATIENTS);

        $term = $this->get('term', '');

        if (empty($term)) {
            $this->json(['results' => []]);
        }

        $patients = $this->patientModel->search($term, 10);

        $results = array_map(function ($patient) {
            return [
                'id' => $patient['id'],
                'text' => $patient['patient_number'] . ' - ' . $patient['first_name'] . ' ' . $patient['last_name'],
                'name' => $patient['first_name'] . ' ' . $patient['last_name'],
                'email' => $patient['email'],
                'phone' => $patient['phone'],
            ];
        }, $patients);

        $this->json(['results' => $results]);
    }
}
