<?php

namespace App\Services;

use App\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PrescriptionPdfService
{
    public function generate(Prescription $prescription)
    {
        $prescription->loadMissing(['patient', 'doctor', 'consultation.complaint']);
        $path = 'prescriptions/' . $prescription->created_at->format('Y') . '/' . $prescription->prescription_number . '.pdf';
        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription'))->setPaper('a4', 'portrait');
        Storage::disk('local')->put($path, $pdf->output());
        $prescription->update(['pdf_path' => $path]);

        return $path;
    }
}
