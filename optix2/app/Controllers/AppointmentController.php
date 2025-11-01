<?php
/**
 * Appointment Controller
 *
 * Handles appointment operations including scheduling, calendar view, reminders
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Helpers\Email;

class AppointmentController extends BaseController
{
    /**
     * @var Appointment Appointment model
     */
    private Appointment $appointmentModel;

    /**
     * @var Patient Patient model
     */
    private Patient $patientModel;

    /**
     * @var User User model
     */
    private User $userModel;

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
        $this->appointmentModel = new Appointment();
        $this->patientModel = new Patient();
        $this->userModel = new User();
        $this->email = new Email();
    }

    /**
     * Calendar view
     *
     * @return void
     */
    public function calendar(): void
    {
        $this->requirePermission('view_appointments');

        $view = $this->get('view', 'month'); // day, week, month
        $date = $this->get('date', date('Y-m-d'));
        $providerId = $this->get('provider_id', null);

        // Get appointments based on view
        $appointments = [];
        switch ($view) {
            case 'day':
                $appointments = $this->appointmentModel->getByDate($date, $providerId);
                break;
            case 'week':
                $startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
                $endDate = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
                $appointments = $this->getAppointmentsByDateRange($startDate, $endDate, $providerId);
                break;
            case 'month':
                $startDate = date('Y-m-01', strtotime($date));
                $endDate = date('Y-m-t', strtotime($date));
                $appointments = $this->getAppointmentsByDateRange($startDate, $endDate, $providerId);
                break;
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $appointments]);
        } else {
            $this->view('appointments/calendar', [
                'appointments' => $appointments,
                'view' => $view,
                'date' => $date,
                'providerId' => $providerId
            ]);
        }
    }

    /**
     * List appointments
     *
     * @return void
     */
    public function list(): void
    {
        $this->requirePermission('view_appointments');

        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $search = $this->get('search', '');
        $status = $this->get('status', null);
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

        if ($status) {
            $where[] = "a.status = ?";
            $params[] = $status;
        }

        if ($providerId) {
            $where[] = "a.provider_id = ?";
            $params[] = $providerId;
        }

        if ($startDate && $endDate) {
            $where[] = "a.appointment_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }

        $whereClause = !empty($where) ? implode(" AND ", $where) : "1=1";

        $appointments = $this->appointmentModel->paginate($page, $perPage, $whereClause, $params, 'appointment_date', 'DESC');

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $appointments]);
        } else {
            $this->view('appointments/list', [
                'appointments' => $appointments,
                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'provider_id' => $providerId,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
        }
    }

    /**
     * Create new appointment
     *
     * @return void
     */
    public function create(): void
    {
        $this->requirePermission('create_appointments');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = [
                'patient_id' => 'required|integer',
                'provider_id' => 'required|integer',
                'location_id' => 'required|integer',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'appointment_type' => 'required',
                'duration' => 'required|integer'
            ];

            if (!$this->validate($this->post(), $rules)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'errors' => $this->getValidationErrors()], 400);
                } else {
                    $this->flashAndRedirect('error', 'Validation failed', $this->back());
                }
            }

            // Check availability
            $available = $this->appointmentModel->checkAvailability(
                $this->post('appointment_date'),
                $this->post('appointment_time'),
                $this->post('provider_id'),
                $this->post('duration')
            );

            if (!$available) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Time slot not available'], 400);
                } else {
                    $this->flashAndRedirect('error', 'Time slot not available', $this->back());
                }
            }

            $data = [
                'patient_id' => $this->post('patient_id'),
                'provider_id' => $this->post('provider_id'),
                'location_id' => $this->post('location_id'),
                'appointment_date' => $this->post('appointment_date'),
                'appointment_time' => $this->post('appointment_time'),
                'duration' => $this->post('duration'),
                'appointment_type' => $this->post('appointment_type'),
                'reason' => $this->post('reason'),
                'notes' => $this->post('notes'),
                'status' => 'scheduled',
                'created_by' => $this->auth->getUserId()
            ];

            try {
                $id = $this->appointmentModel->create($data);
                $this->logActivity('appointment_created', "Created appointment ID: {$id}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Appointment created successfully', 'id' => $id]);
                } else {
                    $this->flashAndRedirect('success', 'Appointment created successfully', APP_URL . '/appointments/list');
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to create appointment'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to create appointment', $this->back());
                }
            }
        } else {
            $this->view('appointments/create');
        }
    }

    /**
     * Edit appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    public function edit(int $id): void
    {
        $this->requirePermission('edit_appointments');

        $appointment = $this->appointmentModel->find($id);

        if (!$appointment) {
            $this->error404('Appointment not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = [
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'duration' => 'required|integer'
            ];

            if (!$this->validate($this->post(), $rules)) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'errors' => $this->getValidationErrors()], 400);
                } else {
                    $this->flashAndRedirect('error', 'Validation failed', $this->back());
                }
            }

            // Check availability if time changed
            if ($this->post('appointment_date') != $appointment['appointment_date'] ||
                $this->post('appointment_time') != $appointment['appointment_time']) {

                $available = $this->appointmentModel->checkAvailability(
                    $this->post('appointment_date'),
                    $this->post('appointment_time'),
                    $appointment['provider_id'],
                    $this->post('duration'),
                    $id
                );

                if (!$available) {
                    if ($this->isAjax()) {
                        $this->json(['success' => false, 'message' => 'Time slot not available'], 400);
                    } else {
                        $this->flashAndRedirect('error', 'Time slot not available', $this->back());
                    }
                }
            }

            $data = [
                'appointment_date' => $this->post('appointment_date'),
                'appointment_time' => $this->post('appointment_time'),
                'duration' => $this->post('duration'),
                'appointment_type' => $this->post('appointment_type'),
                'reason' => $this->post('reason'),
                'notes' => $this->post('notes')
            ];

            try {
                $this->appointmentModel->update($id, $data);
                $this->logActivity('appointment_updated', "Updated appointment ID: {$id}");

                if ($this->isAjax()) {
                    $this->json(['success' => true, 'message' => 'Appointment updated successfully']);
                } else {
                    $this->flashAndRedirect('success', 'Appointment updated successfully', APP_URL . '/appointments/list');
                }
            } catch (\Exception $e) {
                if ($this->isAjax()) {
                    $this->json(['success' => false, 'message' => 'Failed to update appointment'], 500);
                } else {
                    $this->flashAndRedirect('error', 'Failed to update appointment', $this->back());
                }
            }
        } else {
            $this->view('appointments/edit', ['appointment' => $appointment]);
        }
    }

    /**
     * Delete/Cancel appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    public function delete(int $id): void
    {
        $this->requirePermission('delete_appointments');
        $this->requireCsrfToken();

        $appointment = $this->appointmentModel->find($id);

        if (!$appointment) {
            $this->json(['success' => false, 'message' => 'Appointment not found'], 404);
        }

        $reason = $this->post('reason', '');

        try {
            // Soft cancel instead of delete
            $this->appointmentModel->update($id, [
                'status' => 'cancelled',
                'cancelled_at' => date(DATETIME_FORMAT),
                'cancellation_reason' => $reason
            ]);

            $this->logActivity('appointment_cancelled', "Cancelled appointment ID: {$id} - Reason: {$reason}");

            $this->json(['success' => true, 'message' => 'Appointment cancelled successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to cancel appointment'], 500);
        }
    }

    /**
     * Check-in appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    public function checkIn(int $id): void
    {
        $this->requirePermission('checkin_appointments');
        $this->requireCsrfToken();

        $appointment = $this->appointmentModel->find($id);

        if (!$appointment) {
            $this->json(['success' => false, 'message' => 'Appointment not found'], 404);
        }

        if ($appointment['status'] === 'completed') {
            $this->json(['success' => false, 'message' => 'Appointment already completed'], 400);
        }

        try {
            $this->appointmentModel->update($id, [
                'status' => 'checked_in',
                'checked_in_at' => date(DATETIME_FORMAT)
            ]);

            $this->logActivity('appointment_checked_in', "Checked in appointment ID: {$id}");

            $this->json(['success' => true, 'message' => 'Patient checked in successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to check in'], 500);
        }
    }

    /**
     * Complete appointment
     *
     * @param int $id Appointment ID
     * @return void
     */
    public function complete(int $id): void
    {
        $this->requirePermission('complete_appointments');
        $this->requireCsrfToken();

        $appointment = $this->appointmentModel->find($id);

        if (!$appointment) {
            $this->json(['success' => false, 'message' => 'Appointment not found'], 404);
        }

        try {
            $this->appointmentModel->update($id, [
                'status' => 'completed',
                'completed_at' => date(DATETIME_FORMAT)
            ]);

            $this->logActivity('appointment_completed', "Completed appointment ID: {$id}");

            $this->json(['success' => true, 'message' => 'Appointment marked as completed']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to complete appointment'], 500);
        }
    }

    /**
     * Get available time slots
     *
     * @return void
     */
    public function getAvailableSlots(): void
    {
        $this->requirePermission('view_appointments');

        $date = $this->get('date');
        $providerId = (int)$this->get('provider_id');
        $duration = (int)$this->get('duration', 30);

        if (!$date || !$providerId) {
            $this->json(['success' => false, 'message' => 'Missing required parameters'], 400);
        }

        // Get existing appointments for the day
        $appointments = $this->appointmentModel->getByDate($date, $providerId);

        // Working hours (configurable)
        $startHour = 9; // 9 AM
        $endHour = 17; // 5 PM
        $slotDuration = 30; // 30 minutes

        $slots = [];
        $currentTime = strtotime($date . ' ' . str_pad($startHour, 2, '0', STR_PAD_LEFT) . ':00:00');
        $endTime = strtotime($date . ' ' . str_pad($endHour, 2, '0', STR_PAD_LEFT) . ':00:00');

        while ($currentTime < $endTime) {
            $timeStr = date('H:i:00', $currentTime);

            $available = $this->appointmentModel->checkAvailability($date, $timeStr, $providerId, $duration);

            $slots[] = [
                'time' => $timeStr,
                'available' => $available
            ];

            $currentTime = strtotime("+{$slotDuration} minutes", $currentTime);
        }

        $this->json(['success' => true, 'data' => $slots]);
    }

    /**
     * Send appointment reminders
     *
     * @return void
     */
    public function sendReminders(): void
    {
        $this->requirePermission('send_reminders');

        $hoursAhead = (int)$this->get('hours', 24);

        $appointments = $this->appointmentModel->getNeedingReminders($hoursAhead);

        $sent = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            if (!$appointment['patient_email']) {
                $failed++;
                continue;
            }

            $subject = "Appointment Reminder - " . APP_NAME;
            $body = "Dear {$appointment['patient_name']},<br><br>";
            $body .= "This is a reminder for your upcoming appointment:<br><br>";
            $body .= "<strong>Date:</strong> " . date('F j, Y', strtotime($appointment['appointment_date'])) . "<br>";
            $body .= "<strong>Time:</strong> " . date('g:i A', strtotime($appointment['appointment_time'])) . "<br>";
            $body .= "<strong>Provider:</strong> {$appointment['provider_name']}<br>";
            $body .= "<strong>Type:</strong> " . ucfirst(str_replace('_', ' ', $appointment['appointment_type'])) . "<br><br>";

            if ($appointment['reason']) {
                $body .= "<strong>Reason:</strong> {$appointment['reason']}<br><br>";
            }

            $body .= "If you need to reschedule or cancel, please contact us as soon as possible.<br><br>";
            $body .= "Best regards,<br>" . APP_NAME;

            try {
                $emailSent = $this->email->send($appointment['patient_email'], $subject, $body);

                if ($emailSent) {
                    $this->appointmentModel->update($appointment['id'], [
                        'reminder_sent' => true,
                        'reminder_sent_at' => date(DATETIME_FORMAT)
                    ]);
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $failed++;
            }
        }

        $this->logActivity('reminders_sent', "Sent {$sent} appointment reminders, {$failed} failed");

        if ($this->isAjax()) {
            $this->json([
                'success' => true,
                'message' => "Sent {$sent} reminders, {$failed} failed",
                'sent' => $sent,
                'failed' => $failed
            ]);
        } else {
            $this->flashAndRedirect('success', "Sent {$sent} reminders, {$failed} failed", APP_URL . '/appointments/list');
        }
    }

    /**
     * Get appointments by date range
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param int|null $providerId Provider ID
     * @return array
     */
    private function getAppointmentsByDateRange(string $startDate, string $endDate, ?int $providerId = null): array
    {
        $sql = "SELECT a.*,
                CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                p.phone as patient_phone
                FROM appointments a
                LEFT JOIN patients p ON a.patient_id = p.id
                LEFT JOIN users u ON a.provider_id = u.id
                WHERE a.appointment_date BETWEEN ? AND ? AND a.deleted_at IS NULL";

        $params = [$startDate, $endDate];

        if ($providerId) {
            $sql .= " AND a.provider_id = ?";
            $params[] = $providerId;
        }

        $sql .= " ORDER BY a.appointment_date ASC, a.appointment_time ASC";

        return $this->db->select($sql, $params);
    }
}
