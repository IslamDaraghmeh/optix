<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Exam;
use App\Models\Glass;
use App\Models\Sale;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache dashboard statistics for 5 minutes to reduce database load
        $cacheKey = 'dashboard_stats_' . now()->format('Y-m-d_H:i');
        $cacheMinutes = 5;

        $data = Cache::remember($cacheKey, now()->addMinutes($cacheMinutes), function () {
            $today = now()->startOfDay();
            $endOfDay = now()->endOfDay();
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();

            // Optimize today's and month's statistics with single queries per table
            $todayStats = $this->getTodayStatistics($today, $endOfDay);
            $monthStats = $this->getMonthStatistics($startOfMonth, $endOfMonth);

            // Recent activities - select only needed columns
            $recentPatients = Patient::select('id', 'name', 'phone', 'email', 'created_at')
                ->withCount(['exams', 'glasses', 'sales'])
                ->latest('created_at')
                ->limit(5)
                ->get();

            // Pending and ready glasses with optimized queries
            $pendingGlasses = Glass::select('id', 'patient_id', 'frame_type', 'price', 'status', 'created_at')
                ->with('patient:id,name,phone')
                ->where('status', 'pending')
                ->oldest('created_at')
                ->limit(10)
                ->get();

            $readyGlasses = Glass::select('id', 'patient_id', 'frame_type', 'price', 'status', 'updated_at')
                ->with('patient:id,name,phone')
                ->where('status', 'ready')
                ->oldest('updated_at')
                ->limit(10)
                ->get();

            // Outstanding payments with optimized single query
            $outstandingPayments = Sale::select('id', 'patient_id', 'sale_date', 'total_price', 'paid_amount', 'remaining_amount')
                ->with('patient:id,name,phone')
                ->where('remaining_amount', '>', 0)
                ->latest('sale_date')
                ->limit(10)
                ->get();

            $outstandingPaymentsTotal = $outstandingPayments->sum('remaining_amount');

            // Recent expenses - select only needed columns
            $recentExpenses = Expense::select('id', 'category', 'amount', 'expense_date', 'description')
                ->latest('expense_date')
                ->limit(5)
                ->get();

            // Monthly revenue chart data (last 12 months) - optimized query
            $twelveMonthsAgo = now()->subMonths(12)->startOfMonth();
            $revenueData = Sale::selectRaw('DATE_FORMAT(sale_date, "%Y-%m") as month, SUM(total_price) as revenue')
                ->where('sale_date', '>=', $twelveMonthsAgo)
                ->groupBy(DB::raw('DATE_FORMAT(sale_date, "%Y-%m")'))
                ->orderBy(DB::raw('DATE_FORMAT(sale_date, "%Y-%m")'))
                ->pluck('revenue', 'month');

            return compact(
                'todayStats',
                'monthStats',
                'recentPatients',
                'pendingGlasses',
                'readyGlasses',
                'outstandingPayments',
                'outstandingPaymentsTotal',
                'recentExpenses',
                'revenueData'
            );
        });

        return view('dashboard', [
            'todayStats' => $data['todayStats'],
            'monthStats' => $data['monthStats'],
            'recentPatients' => $data['recentPatients'],
            'pendingGlasses' => $data['pendingGlasses'],
            'readyGlasses' => $data['readyGlasses'],
            'outstandingPaymentsList' => $data['outstandingPayments'],
            'outstandingPaymentsTotal' => $data['outstandingPaymentsTotal'],
            'recentExpenses' => $data['recentExpenses'],
            'revenueData' => $data['revenueData']
        ]);
    }

    /**
     * Get today's statistics using optimized queries
     */
    private function getTodayStatistics($today, $endOfDay)
    {
        return [
            'patients' => Patient::whereBetween('created_at', [$today, $endOfDay])->count(),
            'exams' => Exam::whereBetween('exam_date', [$today, $endOfDay])->count(),
            'glasses_ready' => Glass::whereBetween('updated_at', [$today, $endOfDay])
                ->where('status', 'ready')->count(),
            'sales' => Sale::whereBetween('sale_date', [$today, $endOfDay])->count(),
            'revenue' => Sale::whereBetween('sale_date', [$today, $endOfDay])->sum('total_price') ?? 0,
            'expenses' => Expense::whereBetween('expense_date', [$today, $endOfDay])->count(),
            'expense_amount' => Expense::whereBetween('expense_date', [$today, $endOfDay])->sum('amount') ?? 0,
        ];
    }

    /**
     * Get month's statistics using optimized queries
     */
    private function getMonthStatistics($startOfMonth, $endOfMonth)
    {
        return [
            'patients' => Patient::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
            'exams' => Exam::whereBetween('exam_date', [$startOfMonth, $endOfMonth])->count(),
            'glasses_delivered' => Glass::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                ->where('status', 'delivered')->count(),
            'sales' => Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->count(),
            'revenue' => Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])->sum('total_price') ?? 0,
            'expenses' => Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->count(),
            'expense_amount' => Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount') ?? 0,
        ];
    }
}
