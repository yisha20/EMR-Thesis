<?php

namespace App\Services;

use App\Prescription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PrescriptionPdfService
{
    public function generate(Prescription $prescription)
    {
        $prescription->loadMissing(['patient', 'doctor.doctorProfile', 'consultation.complaint']);
        $path = 'prescriptions/' . $prescription->created_at->format('Y') . '/' . $prescription->prescription_number . '.pdf';
        $signatureData = null;
        $profile = optional($prescription->doctor)->doctorProfile;
        if ($profile && $profile->signature_status === 'verified'
            && (int) $profile->signature_version === (int) $prescription->signature_version
            && $profile->signature_path && Storage::disk('local')->exists($profile->signature_path)) {
            $extension = pathinfo($profile->signature_path, PATHINFO_EXTENSION) ?: 'png';
            $signatureData = 'data:image/'.$extension.';base64,'.base64_encode(
                Storage::disk('local')->get($profile->signature_path)
            );
        }
        $logoPath = public_path('img/msu-iit-logo-print.jpg');
        $logoData = is_file($logoPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
        $pdfMode = true;
        $pdf = Pdf::loadView('prescriptions.pdf', compact('prescription', 'signatureData', 'logoData', 'pdfMode'))->setPaper('a4', 'portrait');
        Storage::disk('local')->put($path, $pdf->output());
        $prescription->update(['pdf_path' => $path]);

        return $path;
    }
}
