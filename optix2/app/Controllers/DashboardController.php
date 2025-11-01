<?php
/**
 * Dashboard Controller
 *
 * Handles dashboard display and statistics
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

class DashboardController extends BaseController
{
    /**
     * Display dashboard
     *
     * @return void
     */
    public function index(): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();

        // Get today's date
        $today = date('Y-m-d');

        // Get today's appointments
        $todayAppointments = $this->db->select(
            "SELECT a.*,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    CONCAT(u.first_name, ' ', u.last_name) as provider_name
             FROM appointments a
             LEFT JOIN patients p ON a.patient_id = p.id
             LEFT JOIN users u ON a.provider_id = u.id
             WHERE a.appointment_date = ? AND a.deleted_at IS NULL
             ORDER BY a.appointment_time ASC",
            [$today]
        );

        // Get today's sales summary
        $todaySales = $this->db->selectOne(
            "SELECT COUNT(*) as transaction_count,
                    COALESCE(SUM(total), 0) as total_amount
             FROM transactions
             WHERE transaction_date = ? AND status = 'completed' AND deleted_at IS NULL",
            [$today]
        );

        // Get recent patients (last 10)
        $recentPatients = $this->db->select(
            "SELECT id, patient_number, first_name, last_name, email, phone, created_at
             FROM patients
             WHERE deleted_at IS NULL
             ORDER BY created_at DESC
             LIMIT 10"
        );

        // Get low stock alerts
        $lowStockProducts = $this->db->select(
            "SELECT p.name, p.sku, i.quantity, i.min_quantity, l.name as location_name
             FROM inventory i
             JOIN products p ON i.product_id = p.id
             JOIN locations l ON i.location_id = l.id
             WHERE i.quantity <= i.min_quantity
             ORDER BY i.quantity ASC
             LIMIT 10"
        );

        // Get pending insurance claims
        $pendingClaims = $this->db->select(
            "SELECT ic.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name
             FROM insurance_claims ic
             LEFT JOIN patients p ON ic.patient_id = p.id
             WHERE ic.status IN ('submitted', 'pending') AND ic.deleted_at IS NULL
             ORDER BY ic.claim_date DESC
             LIMIT 10"
        );

        // Get patient statistics
        $patientStats = $this->db->selectOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_last_30_days
             FROM patients
             WHERE deleted_at IS NULL"
        );

        // Get appointment statistics for the week
        $appointmentStats = $this->db->selectOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
             FROM appointments
             WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
               AND deleted_at IS NULL"
        );

        $this->view('dashboard/index', [
            'user' => $user,
            'todayAppointments' => $todayAppointments,
            'todaySales' => $todaySales,
            'recentPatients' => $recentPatients,
            'lowStockProducts' => $lowStockProducts,
            'pendingClaims' => $pendingClaims,
            'patientStats' => $patientStats,
            'appointmentStats' => $appointmentStats,
        ]);
    }
}
