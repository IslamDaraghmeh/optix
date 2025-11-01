<?php
/**
 * Report Model - Handles various business reports and analytics
 */

namespace App\Models;

class Report extends BaseModel
{
    protected string $table = 'transactions';

    public function getDailySalesReport(string $date, ?int $locationId = null): array
    {
        $sql = "SELECT
                COUNT(*) as total_transactions,
                SUM(subtotal) as total_subtotal,
                SUM(tax) as total_tax,
                SUM(total) as total_sales,
                SUM(discount) as total_discounts,
                AVG(total) as average_sale
                FROM {$this->table}
                WHERE transaction_date = ?
                AND status = 'completed'
                AND {$this->deletedAt} IS NULL";

        $params = [$date];

        if ($locationId) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        return $this->db->selectOne($sql, $params);
    }

    public function getSalesReport(string $startDate, string $endDate, ?int $locationId = null): array
    {
        $sql = "SELECT
                DATE(transaction_date) as date,
                COUNT(*) as transactions,
                SUM(total) as total_sales,
                SUM(tax) as total_tax
                FROM {$this->table}
                WHERE transaction_date BETWEEN ? AND ?
                AND status = 'completed'
                AND {$this->deletedAt} IS NULL";

        $params = [$startDate, $endDate];

        if ($locationId) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " GROUP BY DATE(transaction_date) ORDER BY date DESC";

        return $this->db->select($sql, $params);
    }

    public function getProductPerformanceReport(string $startDate, string $endDate, int $limit = 20): array
    {
        $sql = "SELECT
                p.id, p.name, p.sku, p.category,
                SUM(ti.quantity) as total_sold,
                SUM(ti.line_total) as total_revenue,
                AVG(ti.unit_price) as avg_price
                FROM transaction_items ti
                INNER JOIN transactions t ON ti.transaction_id = t.id
                INNER JOIN products p ON ti.product_id = p.id
                WHERE t.transaction_date BETWEEN ? AND ?
                AND t.status = 'completed'
                AND t.deleted_at IS NULL
                GROUP BY p.id
                ORDER BY total_revenue DESC
                LIMIT ?";

        return $this->db->select($sql, [$startDate, $endDate, $limit]);
    }

    public function getInventoryReport(?int $locationId = null): array
    {
        $sql = "SELECT
                p.name, p.sku, p.category,
                i.quantity, i.min_quantity, i.max_quantity,
                p.cost_price, p.selling_price,
                (i.quantity * p.cost_price) as total_cost,
                (i.quantity * p.selling_price) as total_retail,
                l.name as location_name
                FROM products p
                INNER JOIN inventory i ON p.id = i.product_id
                INNER JOIN locations l ON i.location_id = l.id
                WHERE p.deleted_at IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND i.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY p.name ASC";

        return $this->db->select($sql, $params);
    }

    public function getPatientGrowthReport(int $months = 12): array
    {
        $sql = "SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_patients
                FROM patients
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                AND deleted_at IS NULL
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC";

        return $this->db->select($sql, [$months]);
    }

    public function getAppointmentReport(string $startDate, string $endDate, ?int $providerId = null): array
    {
        $sql = "SELECT
                status,
                COUNT(*) as count,
                appointment_type,
                AVG(duration) as avg_duration
                FROM appointments
                WHERE appointment_date BETWEEN ? AND ?
                AND deleted_at IS NULL";

        $params = [$startDate, $endDate];

        if ($providerId) {
            $sql .= " AND provider_id = ?";
            $params[] = $providerId;
        }

        $sql .= " GROUP BY status, appointment_type";

        return $this->db->select($sql, $params);
    }

    public function getFinancialSummary(string $startDate, string $endDate, ?int $locationId = null): array
    {
        $where = $locationId ? "AND location_id = {$locationId}" : "";

        $sales = $this->db->selectOne(
            "SELECT
                SUM(total) as total_revenue,
                SUM(tax) as total_tax,
                SUM(discount) as total_discounts,
                COUNT(*) as total_transactions
             FROM {$this->table}
             WHERE transaction_date BETWEEN ? AND ?
             AND status = 'completed'
             AND {$this->deletedAt} IS NULL {$where}",
            [$startDate, $endDate]
        );

        $payments = $this->db->select(
            "SELECT
                payment_method,
                SUM(amount) as total,
                COUNT(*) as count
             FROM payments p
             INNER JOIN transactions t ON p.transaction_id = t.id
             WHERE DATE(p.payment_date) BETWEEN ? AND ?
             AND p.status = 'completed'
             AND t.deleted_at IS NULL {$where}
             GROUP BY payment_method",
            [$startDate, $endDate]
        );

        return [
            'summary' => $sales,
            'by_payment_method' => $payments
        ];
    }
}
