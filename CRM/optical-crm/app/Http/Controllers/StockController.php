<?php

namespace App\Http\Controllers;

use App\Models\GlassesStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    /**
     * Display a listing of stock items with filtering, searching, and pagination.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = GlassesStock::select(['id', 'item_name', 'item_code', 'item_type', 'brand', 'quantity',
                                           'min_quantity', 'cost_price', 'selling_price', 'supplier', 'location', 'created_at'])
                ->withCount('movements');

            return DataTables::of($query)
                ->addColumn('item_type_badge', function ($stock) {
                    $types = [
                        'frame' => ['label' => 'Frame', 'color' => 'bg-blue-100 text-blue-800'],
                        'lens' => ['label' => 'Lens', 'color' => 'bg-green-100 text-green-800'],
                        'contact_lens' => ['label' => 'Contact Lens', 'color' => 'bg-purple-100 text-purple-800'],
                        'accessory' => ['label' => 'Accessory', 'color' => 'bg-yellow-100 text-yellow-800'],
                        'other' => ['label' => 'Other', 'color' => 'bg-gray-100 text-gray-800'],
                    ];
                    $type = $types[$stock->item_type] ?? ['label' => ucfirst($stock->item_type), 'color' => 'bg-gray-100 text-gray-800'];
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $type['color'] . '">' . $type['label'] . '</span>';
                })
                ->addColumn('status_badge', function ($stock) {
                    if ($stock->quantity == 0) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>';
                    } elseif ($stock->quantity <= $stock->min_quantity) {
                        return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Low Stock</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">In Stock</span>';
                })
                ->addColumn('quantity_info', function ($stock) {
                    $color = $stock->quantity == 0 ? 'text-red-600' : ($stock->quantity <= $stock->min_quantity ? 'text-yellow-600' : 'text-green-600');
                    return '<div class="text-sm">
                        <div><strong class="' . $color . '">Current:</strong> ' . $stock->quantity . '</div>
                        <div><strong>Min:</strong> ' . $stock->min_quantity . '</div>
                    </div>';
                })
                ->addColumn('prices', function ($stock) {
                    return '<div class="text-sm">
                        <div><strong>Cost:</strong> $' . number_format($stock->cost_price, 2) . '</div>
                        <div><strong>Sell:</strong> $' . number_format($stock->selling_price, 2) . '</div>
                    </div>';
                })
                ->addColumn('brand_supplier', function ($stock) {
                    $brand = $stock->brand ? '<div><strong>Brand:</strong> ' . $stock->brand . '</div>' : '';
                    $supplier = $stock->supplier ? '<div><strong>Supplier:</strong> ' . $stock->supplier . '</div>' : '';
                    return '<div class="text-sm text-gray-600">' . $brand . $supplier . '</div>';
                })
                ->addColumn('action', function ($stock) {
                    $showUrl = route('stock.show', $stock->id);
                    $editUrl = route('stock.edit', $stock->id);
                    $deleteUrl = route('stock.destroy', $stock->id);

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
                ->editColumn('created_at', function ($stock) {
                    return $stock->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action', 'item_type_badge', 'status_badge', 'quantity_info', 'prices', 'brand_supplier'])
                ->make(true);
        }

        return view('stock.index');
    }

    /**
     * Show the form for creating a new stock item.
     */
    public function create()
    {
        return view('stock.create');
    }

    /**
     * Store a newly created stock item in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:glasses_stock,item_code',
            'item_type' => 'required|string|in:frame,lens,contact_lens,accessory,other',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ], [
            'item_name.required' => 'Item name is required.',
            'item_code.required' => 'Item code is required.',
            'item_code.unique' => 'This item code already exists.',
            'item_type.required' => 'Item type is required.',
            'item_type.in' => 'Invalid item type selected.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 0.',
            'min_quantity.required' => 'Minimum quantity is required.',
            'cost_price.required' => 'Cost price is required.',
            'selling_price.required' => 'Selling price is required.',
        ]);

        try {
            DB::beginTransaction();

            $stock = GlassesStock::create($validated);

            // Create initial stock movement if quantity > 0
            if ($validated['quantity'] > 0) {
                StockMovement::create([
                    'stock_id' => $stock->id,
                    'movement_type' => 'in',
                    'quantity' => $validated['quantity'],
                    'quantity_before' => 0,
                    'quantity_after' => $validated['quantity'],
                    'notes' => 'Initial stock',
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('stock.show', $stock)
                ->with('success', 'Stock item created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create stock item: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified stock item with movement history.
     */
    public function show($id)
    {
        $stock = GlassesStock::findOrFail($id);

        // Load movements with user information
        $movements = $stock->movements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate movement statistics
        $movementStats = [
            'total_in' => $stock->movements()->where('movement_type', 'in')->sum('quantity'),
            'total_out' => abs($stock->movements()->where('movement_type', 'out')->sum('quantity')),
            'total_adjustments' => $stock->movements()->where('movement_type', 'adjustment')->count(),
        ];

        return view('stock.show', compact('stock', 'movements', 'movementStats'));
    }

    /**
     * Show the form for editing the specified stock item.
     */
    public function edit($id)
    {
        $stock = GlassesStock::findOrFail($id);
        return view('stock.edit', compact('stock'));
    }

    /**
     * Update the specified stock item in storage.
     */
    public function update(Request $request, $id)
    {
        $stock = GlassesStock::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('glasses_stock', 'item_code')->ignore($stock->id)
            ],
            'item_type' => 'required|string|in:frame,lens,contact_lens,accessory,other',
            'brand' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'min_quantity' => 'required|integer|min:0',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ], [
            'item_name.required' => 'Item name is required.',
            'item_code.required' => 'Item code is required.',
            'item_code.unique' => 'This item code already exists.',
            'item_type.required' => 'Item type is required.',
            'item_type.in' => 'Invalid item type selected.',
            'min_quantity.required' => 'Minimum quantity is required.',
            'cost_price.required' => 'Cost price is required.',
            'selling_price.required' => 'Selling price is required.',
        ]);

        try {
            $stock->update($validated);

            return redirect()->route('stock.show', $stock)
                ->with('success', 'Stock item updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update stock item: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified stock item from storage.
     */
    public function destroy($id)
    {
        $stock = GlassesStock::findOrFail($id);

        try {
            // Check if stock has any movements
            if ($stock->movements()->count() > 0) {
                return back()->with('error', 'Cannot delete stock item with existing movements. Please adjust quantity to 0 first.');
            }

            $stock->delete();

            return redirect()->route('stock.index')
                ->with('success', 'Stock item deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete stock item: ' . $e->getMessage());
        }
    }

    /**
     * Show the form to add stock quantity.
     */
    public function addStock($id)
    {
        $stock = GlassesStock::findOrFail($id);
        return view('stock.add-stock', compact('stock'));
    }

    /**
     * Process adding stock quantity.
     */
    public function processAddStock(Request $request, $id)
    {
        $stock = GlassesStock::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
        ]);

        try {
            DB::beginTransaction();

            $stock->addStock(
                $validated['quantity'],
                $validated['notes'] ?? null,
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null
            );

            DB::commit();

            return redirect()->route('stock.show', $stock)
                ->with('success', "Successfully added {$validated['quantity']} units to stock. New quantity: {$stock->quantity}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to add stock: ' . $e->getMessage());
        }
    }

    /**
     * Show the form to remove stock quantity.
     */
    public function removeStock($id)
    {
        $stock = GlassesStock::findOrFail($id);
        return view('stock.remove-stock', compact('stock'));
    }

    /**
     * Process removing stock quantity.
     */
    public function processRemoveStock(Request $request, $id)
    {
        $stock = GlassesStock::findOrFail($id);

        $validated = $request->validate([
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $stock->quantity
            ],
            'notes' => 'nullable|string|max:1000',
            'reference_type' => 'nullable|string|max:100',
            'reference_id' => 'nullable|integer',
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => "Cannot remove more than available stock ({$stock->quantity} units).",
        ]);

        try {
            DB::beginTransaction();

            $stock->removeStock(
                $validated['quantity'],
                $validated['notes'] ?? null,
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null
            );

            DB::commit();

            return redirect()->route('stock.show', $stock)
                ->with('success', "Successfully removed {$validated['quantity']} units from stock. New quantity: {$stock->quantity}");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to remove stock: ' . $e->getMessage());
        }
    }

    /**
     * Show the form to adjust stock quantity.
     */
    public function adjustStock($id)
    {
        $stock = GlassesStock::findOrFail($id);
        return view('stock.adjust-stock', compact('stock'));
    }

    /**
     * Process stock quantity adjustment.
     */
    public function processAdjustStock(Request $request, $id)
    {
        $stock = GlassesStock::findOrFail($id);

        $validated = $request->validate([
            'new_quantity' => 'required|integer|min:0',
            'notes' => 'required|string|max:1000',
        ], [
            'new_quantity.required' => 'New quantity is required.',
            'new_quantity.min' => 'New quantity cannot be negative.',
            'notes.required' => 'Notes are required for stock adjustments to maintain audit trail.',
        ]);

        try {
            DB::beginTransaction();

            $oldQuantity = $stock->quantity;
            $stock->adjustStock($validated['new_quantity'], $validated['notes']);

            DB::commit();

            $difference = $validated['new_quantity'] - $oldQuantity;
            $action = $difference > 0 ? 'increased' : ($difference < 0 ? 'decreased' : 'unchanged');

            return redirect()->route('stock.show', $stock)
                ->with('success', "Stock adjusted successfully. Quantity {$action} from {$oldQuantity} to {$validated['new_quantity']}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to adjust stock: ' . $e->getMessage());
        }
    }

    /**
     * Display all stock movements with filtering.
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['stock', 'user']);

        // Filter by movement type
        if ($request->filled('movement_type') && $request->movement_type !== 'all') {
            $query->where('movement_type', $request->movement_type);
        }

        // Filter by stock item
        if ($request->filled('stock_id')) {
            $query->where('stock_id', $request->stock_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by notes or reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhere('reference_type', 'like', "%{$search}%")
                    ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get all stock items for filter dropdown
        $stockItems = GlassesStock::orderBy('item_name')->get();

        // Calculate statistics
        $stats = [
            'total_movements' => StockMovement::count(),
            'stock_in' => StockMovement::where('movement_type', 'in')->sum('quantity'),
            'stock_out' => abs(StockMovement::where('movement_type', 'out')->sum('quantity')),
            'adjustments' => StockMovement::where('movement_type', 'adjustment')->count(),
        ];

        return view('stock.movements', compact('movements', 'stockItems', 'stats'));
    }
}
