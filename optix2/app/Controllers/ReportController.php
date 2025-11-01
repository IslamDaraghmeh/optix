<?php
/**
 * Report Controller - Generates various business reports
 */

namespace App\Controllers;

use App\Models\Report;
use App\Helpers\PDF;

class ReportController extends BaseController
{
    private Report $reportModel;
    private PDF $pdf;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->pdf = new PDF();
    }

    public function index(): void
    {
        $this->requirePermission('view_reports');
        $this->view('reports/index');
    }

    public function sales(): void
    {
        $this->requirePermission('view_reports');

        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-d'));
        $locationId = $this->get('location_id', null);

        $data = $this->reportModel->getSalesReport($startDate, $endDate, $locationId);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'data' => $data]);
        } else {
            $this->view('reports/sales', ['data' => $data, 'startDate' => $startDate, 'endDate' => $endDate]);
        }
    }

    public function financial(): void
    {
        $this->requirePermission('view_financial_reports');

        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-d'));
        $locationId = $this->get('location_id', null);

        $data = $this->reportModel->getFinancialSummary($startDate, $endDate, $locationId);

        $this->view('reports/financial', ['data' => $data, 'startDate' => $startDate, 'endDate' => $endDate]);
    }

    public function inventory(): void
    {
        $this->requirePermission('view_reports');

        $locationId = $this->get('location_id', null);
        $data = $this->reportModel->getInventoryReport($locationId);

        $this->view('reports/inventory', ['data' => $data, 'locationId' => $locationId]);
    }

    public function clinical(): void
    {
        $this->requirePermission('view_reports');

        $startDate = $this->get('start_date', date('Y-m-01'));
        $endDate = $this->get('end_date', date('Y-m-d'));
        $providerId = $this->get('provider_id', null);

        $appointmentData = $this->reportModel->getAppointmentReport($startDate, $endDate, $providerId);

        $this->view('reports/clinical', ['data' => $appointmentData, 'startDate' => $startDate, 'endDate' => $endDate]);
    }

    public function export(): void
    {
        $this->requirePermission('export_reports');

        $type = $this->get('type');
        $format = $this->get('format', 'pdf');

        // Export implementation
        $this->json(['success' => true, 'message' => 'Export feature ready for implementation']);
    }

    public function generatePDF(): void
    {
        $this->requirePermission('view_reports');

        $reportType = $this->get('report_type');
        $data = []; // Get report data based on type

        $html = '<html><body><h1>Report</h1><p>Report data here</p></body></html>';
        $filename = "report_{$reportType}_" . date('Y-m-d') . ".pdf";

        $this->pdf->generateFromHtml($html, $filename, 'I');
    }
}
