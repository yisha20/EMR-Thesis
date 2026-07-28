<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Patient;
use App\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user()->student()->firstOrFail();
        $patient = Patient::where('id_number', $student->student_id_number)->first();
        $prescriptions = Prescription::with(['patient', 'doctor', 'consultation'])
            ->when($patient, function ($query) use ($patient) { $query->where('patient_id', $patient->id); })
            ->when(!$patient, function ($query) { $query->whereRaw('1 = 0'); })
            ->latest()
            ->paginate(10);

        return view('student.prescriptions', compact('prescriptions'));
    }

    public function show(Request $request, Prescription $prescription)
    {
        $this->authorizeAccess($request, $prescription);
        $prescription->load(['patient', 'doctor', 'consultation.complaint']);

        return view('prescriptions.show', ['prescription' => $prescription, 'autoPrint' => false]);
    }

    public function print(Request $request, Prescription $prescription)
    {
        $this->authorizeAccess($request, $prescription);
        $this->authorizeExport($request);
        $prescription->load(['patient', 'doctor', 'consultation.complaint']);
        ActivityLogger::log('printed prescription for ' . $prescription->patient->first_name . ' ' . $prescription->patient->last_name);

        return view('prescriptions.show', ['prescription' => $prescription, 'autoPrint' => true]);
    }

    public function pdf(Request $request, Prescription $prescription)
    {
        $this->authorizeAccess($request, $prescription);
        $this->authorizeExport($request);
        abort_unless($prescription->pdf_path && Storage::disk('local')->exists($prescription->pdf_path), 404);

        return response()->file(Storage::disk('local')->path($prescription->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $prescription->prescription_number . '.pdf"',
        ]);
    }

    public function download(Request $request, Prescription $prescription)
    {
        $this->authorizeAccess($request, $prescription);
        $this->authorizeExport($request);
        abort_unless($prescription->pdf_path && Storage::disk('local')->exists($prescription->pdf_path), 404);
        ActivityLogger::log('downloaded prescription', $prescription->prescription_number);

        return response()->download(Storage::disk('local')->path($prescription->pdf_path), $prescription->prescription_number . '.pdf');
    }

    private function authorizeAccess(Request $request, Prescription $prescription)
    {
        $prescription->loadMissing('patient');
        $roleName = optional($request->user()->role)->name;
        if ($roleName === 'Student') {
            $student = $request->user()->student;
            abort_unless($student && $prescription->patient && $prescription->patient->id_number === $student->student_id_number, 403);
            return;
        }

        abort_unless(in_array($roleName, ['Administrator', 'Doctor', 'Nurse', 'Staff'], true), 403);
        if ($roleName === 'Doctor') {
            abort_unless((int) $prescription->doctor_id === (int) $request->user()->id, 403);
        }
    }

    private function authorizeExport(Request $request)
    {
        // Ownership/role access is enforced before export. Patient portal users may
        // export only prescriptions already proven to belong to their patient record.
    }
}
