<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Exam;
use App\Models\Glass;
use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Display sales reports.
     */
    public function sales(Request $request)
    {
        $query = Sale::select('id', 'patient_id', 'sale_date', 'total_price', 'paid_amount', 'remaining_amount', 'payment_method')
            ->with('patient:id,name,phone');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Clone query for statistics before pagination
        $statsQuery = clone $query;

        // Calculate summary statistics
        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_count,
            SUM(total_price) as total_sales,
            SUM(paid_amount) as total_paid,
            SUM(remaining_amount) as total_remaining
        ')->first();

        $totalSales = $stats->total_sales ?? 0;
        $totalPaid = $stats->total_paid ?? 0;
        $totalRemaining = $stats->total_remaining ?? 0;
        $totalCount = $stats->total_count ?? 0;

        // Paginate results for better performance
        $sales = $query->latest('sale_date')->paginate(50)->withQueryString();

        // Get patients list with caching
        $patients = \Cache::remember('patients_list', 3600, function () {
            return Patient::select('id', 'name')->orderBy('name')->get();
        });

        return view('reports.sales', compact('sales', 'patients', 'totalSales', 'totalPaid', 'totalRemaining', 'totalCount'));
    }

    /**
     * Display patient reports.
     */
    public function patients(Request $request)
    {
        $query = Patient::select('id', 'name', 'phone', 'email', 'created_at')
            ->withCount(['exams', 'glasses', 'sales']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Clone for statistics
        $statsQuery = clone $query;

        // Get totals using database aggregation
        $totalPatients = $statsQuery->count();

        // Paginate results
        $patients = $query->latest('created_at')->paginate(50)->withQueryString();

        // Calculate summary from paginated results
        $totalExams = $patients->sum('exams_count');
        $totalGlasses = $patients->sum('glasses_count');
        $totalSales = $patients->sum('sales_count');

        return view('reports.patients', compact('patients', 'totalPatients', 'totalExams', 'totalGlasses', 'totalSales'));
    }

    /**
     * Display glasses reports.
     */
    public function glasses(Request $request)
    {
        $query = Glass::select('id', 'patient_id', 'frame_type', 'lens_type', 'price', 'status', 'created_at')
            ->with('patient:id,name,phone');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Clone for statistics
        $statsQuery = clone $query;

        // Get summary statistics using efficient aggregation
        $stats = $statsQuery->selectRaw('
            COUNT(*) as total_glasses,
            SUM(price) as total_value
        ')->first();

        $totalGlasses = $stats->total_glasses ?? 0;
        $totalValue = $stats->total_value ?? 0;

        // Get status counts separately
        $statusCounts = Glass::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Paginate results
        $glasses = $query->latest('created_at')->paginate(50)->withQueryString();

        return view('reports.glasses', compact('glasses', 'totalGlasses', 'totalValue', 'statusCounts'));
    }

    /**
     * Export reports.
     */
    public function export(Request $request, $type)
    {
        switch ($type) {
            case 'sales':
                return $this->exportSales($request);
            case 'patients':
                return $this->exportPatients($request);
            case 'glasses':
                return $this->exportGlasses($request);
            default:
                abort(404);
        }
    }

    /**
     * Export sales report to PDF.
     */
    private function exportSales($request)
    {
        $query = Sale::with('patient');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();

        $pdf = Pdf::loadView('reports.exports.sales-pdf', compact('sales'));
        return $pdf->download('sales_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export patients report to PDF.
     */
    private function exportPatients($request)
    {
        $query = Patient::withCount(['exams', 'glasses', 'sales']);

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $patients = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.exports.patients-pdf', compact('patients'));
        return $pdf->download('patients_report_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export glasses report to PDF.
     */
    private function exportGlasses($request)
    {
        $query = Glass::with('patient');

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $glasses = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.exports.glasses-pdf', compact('glasses'));
        return $pdf->download('glasses_report_' . date('Y-m-d') . '.pdf');
    }
}
