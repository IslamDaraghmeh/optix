<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Patient;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Sale::select(['id', 'patient_id', 'items', 'total_price', 'paid_amount', 'remaining_amount', 'sale_date', 'created_at'])
                ->with('patient:id,name,phone');

            return DataTables::of($query)
                ->addColumn('patient_name', function ($sale) {
                    return $sale->patient ? $sale->patient->name : 'Walk-in Customer';
                })
                ->addColumn('patient_phone', function ($sale) {
                    return $sale->patient ? $sale->patient->phone : '-';
                })
                ->addColumn('items_count', function ($sale) {
                    $itemsArray = is_array($sale->items) ? $sale->items : [];
                    return count($itemsArray) . ' item(s)';
                })
                ->addColumn('payment_status_badge', function ($sale) {
                    if ($sale->remaining_amount <= 0) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Paid</span>';
                    } elseif ($sale->paid_amount > 0) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Partial</span>';
                    } else {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Unpaid</span>';
                    }
                })
                ->addColumn('amounts', function ($sale) {
                    return '<div class="text-sm">
                        <div><strong>Total:</strong> $' . number_format($sale->total_price, 2) . '</div>
                        <div><strong>Paid:</strong> $' . number_format($sale->paid_amount, 2) . '</div>
                        <div><strong>Remaining:</strong> $' . number_format($sale->remaining_amount, 2) . '</div>
                    </div>';
                })
                ->addColumn('action', function ($sale) {
                    $showUrl = route('sales.show', $sale->id);
                    $editUrl = route('sales.edit', $sale->id);
                    $deleteUrl = route('sales.destroy', $sale->id);

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
                ->editColumn('sale_date', function ($sale) {
                    return $sale->sale_date->format('Y-m-d');
                })
                ->editColumn('created_at', function ($sale) {
                    return $sale->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action', 'payment_status_badge', 'amounts'])
                ->make(true);
        }

        return view('sales.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $selectedPatientId = $request->get('patient_id');

        return view('sales.create', compact('patients', 'selectedPatientId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
        ]);

        // Calculate remaining amount
        $validated['remaining_amount'] = $validated['total_price'] - $validated['paid_amount'];

        $sale = Sale::create($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Sale recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load('patient');
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $patients = Patient::orderBy('name')->get();
        return view('sales.edit', compact('sale', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
        ]);

        // Calculate remaining amount
        $validated['remaining_amount'] = $validated['total_price'] - $validated['paid_amount'];

        $sale->update($validated);

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }
}
