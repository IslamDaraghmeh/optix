<?php

namespace App\Http\Controllers;

use App\Application\Services\GlassOrderService;
use App\Domain\Glasses\DTOs\GlassOrderSearchDTO;
use App\Domain\Glasses\Models\GlassStatus;
use App\Models\Patient;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GlassController extends Controller
{
    public function __construct(
        private GlassOrderService $glassOrderService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = \App\Models\Glass::select(['id', 'patient_id', 'lens_type', 'frame_type', 'price', 'status', 'created_at'])
                ->with('patient:id,name,phone');

            return DataTables::of($query)
                ->addColumn('patient_name', function ($glass) {
                    return $glass->patient ? $glass->patient->name : '-';
                })
                ->addColumn('patient_phone', function ($glass) {
                    return $glass->patient ? $glass->patient->phone : '-';
                })
                ->addColumn('status_badge', function ($glass) {
                    $badges = [
                        'pending' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>',
                        'ready' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Ready</span>',
                        'delivered' => '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Delivered</span>',
                    ];
                    return $badges[$glass->status] ?? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">' . ucfirst($glass->status) . '</span>';
                })
                ->addColumn('price_formatted', function ($glass) {
                    return '$' . number_format($glass->price, 2);
                })
                ->addColumn('action', function ($glass) {
                    $showUrl = route('glasses.show', $glass->id);
                    $editUrl = route('glasses.edit', $glass->id);
                    $deleteUrl = route('glasses.destroy', $glass->id);

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
                ->editColumn('created_at', function ($glass) {
                    return $glass->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action', 'status_badge'])
                ->make(true);
        }

        return view('glasses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $selectedPatientId = $request->get('patient_id');

        // Get available options for dropdowns
        $lensTypes = $this->glassOrderService->getAvailableLensTypes();
        $frameTypes = $this->glassOrderService->getAvailableFrameTypes();

        return view('glasses.create', compact('patients', 'selectedPatientId', 'lensTypes', 'frameTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lens_type' => 'required|string|max:255',
            'frame_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status' => 'nullable|in:pending,ready,delivered',
        ]);

        try {
            $createDTO = \App\Domain\Glasses\DTOs\CreateGlassOrderDTO::fromArray($validated);
            $this->glassOrderService->createGlassOrder($createDTO);

            return redirect()->route('glasses.index')
                ->with('success', 'Glasses order created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        try {
            $glassOrderDTO = $this->glassOrderService->getGlassOrder($id);
            return view('glasses.show', compact('glassOrderDTO'));
        } catch (\Exception $e) {
            return redirect()->route('glasses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        try {
            $glassOrderDTO = $this->glassOrderService->getGlassOrder($id);
            $patients = Patient::orderBy('name')->get();
            $lensTypes = $this->glassOrderService->getAvailableLensTypes();
            $frameTypes = $this->glassOrderService->getAvailableFrameTypes();

            return view('glasses.edit', compact('glassOrderDTO', 'patients', 'lensTypes', 'frameTypes'));
        } catch (\Exception $e) {
            return redirect()->route('glasses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lens_type' => 'required|string|max:255',
            'frame_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:pending,ready,delivered',
        ]);

        try {
            $updateDTO = \App\Domain\Glasses\DTOs\UpdateGlassOrderDTO::fromArray($validated);
            $this->glassOrderService->updateGlassOrder($id, $updateDTO);

            return redirect()->route('glasses.index')
                ->with('success', 'Glasses order updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $this->glassOrderService->deleteGlassOrder($id);

            return redirect()->route('glasses.index')
                ->with('success', 'Glasses order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('glasses.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update glasses status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,ready,delivered',
        ]);

        try {
            $status = GlassStatus::fromString($validated['status']);
            $this->glassOrderService->updateGlassOrderStatus($id, $status);

            return redirect()->route('glasses.index')
                ->with('success', 'Glasses status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('glasses.index')
                ->with('error', $e->getMessage());
        }
    }
}
