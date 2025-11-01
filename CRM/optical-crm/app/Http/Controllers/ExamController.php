<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Patient;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class ExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Exam::select(['id', 'patient_id', 'exam_date', 'right_eye_sphere', 'right_eye_cylinder',
                                   'right_eye_axis', 'left_eye_sphere', 'left_eye_cylinder', 'left_eye_axis',
                                   'notes', 'created_at'])
                ->with('patient:id,name,phone');

            return DataTables::of($query)
                ->addColumn('patient_name', function ($exam) {
                    return $exam->patient ? $exam->patient->name : '-';
                })
                ->addColumn('patient_phone', function ($exam) {
                    return $exam->patient ? $exam->patient->phone : '-';
                })
                ->addColumn('right_eye', function ($exam) {
                    $sphere = $exam->right_eye_sphere ?? '-';
                    $cylinder = $exam->right_eye_cylinder ?? '-';
                    $axis = $exam->right_eye_axis ?? '-';
                    return '<span class="text-sm text-gray-700">SPH: ' . $sphere . ' | CYL: ' . $cylinder . ' | AXIS: ' . $axis . '</span>';
                })
                ->addColumn('left_eye', function ($exam) {
                    $sphere = $exam->left_eye_sphere ?? '-';
                    $cylinder = $exam->left_eye_cylinder ?? '-';
                    $axis = $exam->left_eye_axis ?? '-';
                    return '<span class="text-sm text-gray-700">SPH: ' . $sphere . ' | CYL: ' . $cylinder . ' | AXIS: ' . $axis . '</span>';
                })
                ->addColumn('action', function ($exam) {
                    $showUrl = route('exams.show', $exam->id);
                    $editUrl = route('exams.edit', $exam->id);
                    $deleteUrl = route('exams.destroy', $exam->id);

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
                ->editColumn('exam_date', function ($exam) {
                    return $exam->exam_date->format('Y-m-d');
                })
                ->editColumn('created_at', function ($exam) {
                    return $exam->created_at->format('Y-m-d H:i');
                })
                ->rawColumns(['action', 'right_eye', 'left_eye'])
                ->make(true);
        }

        return view('exams.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::orderBy('name')->get();
        $selectedPatientId = $request->get('patient_id');

        return view('exams.create', compact('patients', 'selectedPatientId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_date' => 'required|date',
            'right_eye_sphere' => 'nullable|numeric',
            'right_eye_cylinder' => 'nullable|numeric',
            'right_eye_axis' => 'nullable|integer|min:0|max:180',
            'left_eye_sphere' => 'nullable|numeric',
            'left_eye_cylinder' => 'nullable|numeric',
            'left_eye_axis' => 'nullable|integer|min:0|max:180',
            'notes' => 'nullable|string|max:1000',
        ]);

        $exam = Exam::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Eye exam recorded successfully.',
                'exam' => $exam
            ]);
        }

        return redirect()->route('patients.show', $exam->patient)
            ->with('success', 'Eye exam recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Exam $exam)
    {
        $exam->load('patient');
        return view('exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exam $exam)
    {
        $patients = Patient::orderBy('name')->get();
        return view('exams.edit', compact('exam', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exam $exam)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_date' => 'required|date',
            'right_eye_sphere' => 'nullable|numeric',
            'right_eye_cylinder' => 'nullable|numeric',
            'right_eye_axis' => 'nullable|integer|min:0|max:180',
            'left_eye_sphere' => 'nullable|numeric',
            'left_eye_cylinder' => 'nullable|numeric',
            'left_eye_axis' => 'nullable|integer|min:0|max:180',
            'notes' => 'nullable|string|max:1000',
        ]);

        $exam->update($validated);

        return redirect()->route('patients.show', $exam->patient)
            ->with('success', 'Eye exam updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exam $exam)
    {
        $patient = $exam->patient;
        $exam->delete();

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Eye exam deleted successfully.');
    }

    /**
     * Generate prescription PDF.
     */
    public function prescription(Exam $exam)
    {
        $exam->load('patient');

        $pdf = Pdf::loadView('exams.prescription', compact('exam'));
        return $pdf->download('prescription_' . $exam->patient->name . '_' . $exam->exam_date->format('Y-m-d') . '.pdf');
    }
}
