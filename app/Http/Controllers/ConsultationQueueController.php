<?php

namespace App\Http\Controllers;

use App\Consultation;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationQueueController extends Controller
{
    public function callStudent(Request $request, Consultation $consultation)
    {
        DB::transaction(function () use ($request, $consultation) {
            $consultation = Consultation::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            abort_unless($consultation->status === 'Pending Consultation', 422, 'This student is no longer waiting to be called.');
            $consultation->update([
                'status' => 'Called',
                'called_at' => now(),
                'called_by' => $request->user()->id,
            ]);
            optional($consultation->medicalRecord)->update(['consultation_status' => 'Called', 'outcome' => 'Called']);
            ActivityLogger::log('called ' . $consultation->complaint->student_name . ' for consultation');
        });

        return redirect()->back()->with('success', 'Student marked as called for consultation.');
    }
}
