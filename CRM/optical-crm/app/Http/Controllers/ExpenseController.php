<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Expense::select(['id', 'title', 'description', 'amount', 'category', 'expense_date',
                                      'payment_method', 'receipt_number', 'vendor', 'notes', 'created_at']);

            return DataTables::of($query)
                ->addColumn('category_badge', function ($expense) {
                    $categories = Expense::getCategories();
                    $categoryName = $categories[$expense->category] ?? ucfirst($expense->category);
                    $colors = [
                        'office_supplies' => 'bg-blue-100 text-blue-800',
                        'equipment' => 'bg-purple-100 text-purple-800',
                        'utilities' => 'bg-green-100 text-green-800',
                        'rent' => 'bg-red-100 text-red-800',
                        'marketing' => 'bg-pink-100 text-pink-800',
                        'professional_services' => 'bg-indigo-100 text-indigo-800',
                        'travel' => 'bg-yellow-100 text-yellow-800',
                        'maintenance' => 'bg-orange-100 text-orange-800',
                        'insurance' => 'bg-teal-100 text-teal-800',
                        'other' => 'bg-gray-100 text-gray-800',
                    ];
                    $color = $colors[$expense->category] ?? 'bg-gray-100 text-gray-800';
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . $categoryName . '</span>';
                })
                ->addColumn('payment_method_badge', function ($expense) {
                    $methods = Expense::getPaymentMethods();
                    $methodName = $methods[$expense->payment_method] ?? ucfirst($expense->payment_method);
                    return '<span class="text-sm text-gray-700">' . $methodName . '</span>';
                })
                ->addColumn('amount_formatted', function ($expense) {
                    return '<span class="font-semibold text-gray-900">$' . number_format($expense->amount, 2) . '</span>';
                })
                ->addColumn('vendor_info', function ($expense) {
                    $vendor = $expense->vendor ? '<div><strong>Vendor:</strong> ' . $expense->vendor . '</div>' : '';
                    $receipt = $expense->receipt_number ? '<div><strong>Receipt:</strong> ' . $expense->receipt_number . '</div>' : '';
                    return '<div class="text-sm text-gray-600">' . $vendor . $receipt . '</div>';
                })
                ->addColumn('action', function ($expense) {
                    $showUrl = route('expenses.show', $expense->id);
                    $editUrl = route('expenses.edit', $expense->id);
                    $deleteUrl = route('expenses.destroy', $expense->id);

                    return '<div class="flex space-x-2">
                        <a href="' . $showUrl . '" class="text-blue-600 hover:text-blue-900" title="' . __('View') . '">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        <a href="' . $editUrl . '" class="text-green-600 hover:text-green-900" title="' . __('Edit') . '">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <form action="' . $deleteUrl . '" method="POST" class="inline" onsubmit="return confirm(\'' . __('Are you sure?') . '\')">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 hover:text-red-900" title="' . __('Delete') . '">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>';
                })
                ->editColumn('expense_date', function ($expense) {
                    return $expense->expense_date->format('Y-m-d');
                })
                ->editColumn('created_at', function ($expense) {
                    return $expense->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action', 'category_badge', 'payment_method_badge', 'amount_formatted', 'vendor_info'])
                ->make(true);
        }

        return view('expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Expense::create($request->all());

        return redirect()->route('expenses.index')
            ->with('success', __('app.created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $expense->update($request->all());

        return redirect()->route('expenses.index')
            ->with('success', __('app.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', __('app.deleted_successfully'));
    }

    /**
     * API endpoint to get expenses
     */
    public function apiIndex(Request $request)
    {
        $query = Expense::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('vendor', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $expenses
        ]);
    }

    /**
     * API endpoint to store expense
     */
    public function apiStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $expense = Expense::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $expense,
            'message' => __('app.created_successfully')
        ]);
    }

    /**
     * API endpoint to update expense
     */
    public function apiUpdate(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'payment_method' => 'required|string',
            'receipt_number' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $expense->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $expense,
            'message' => __('app.updated_successfully')
        ]);
    }

    /**
     * API endpoint to delete expense
     */
    public function apiDestroy(Expense $expense)
    {
        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => __('app.deleted_successfully')
        ]);
    }
}
