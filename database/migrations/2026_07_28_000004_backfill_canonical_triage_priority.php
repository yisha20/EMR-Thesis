<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillCanonicalTriagePriority extends Migration
{
    public function up()
    {
        $updated = 0;
        $conflicts = 0;
        DB::table('student_complaints')->where(function ($query) {
            $query->whereNull('triage_priority')->orWhere('triage_priority', 'unassigned');
        })->orderBy('id')->chunkById(200, function ($complaints) use (&$updated, &$conflicts) {
            foreach ($complaints as $complaint) {
                $queueValues = DB::table('clinic_queues')->where('student_complaint_id', $complaint->id)
                    ->whereIn('priority', ['low', 'moderate', 'high', 'urgent'])
                    ->orderByRaw("CASE WHEN status IN ('waiting','called','serving') THEN 0 ELSE 1 END")
                    ->orderByDesc('id')->pluck('priority')->map('strtolower')->unique()->values();
                $consultation = strtolower((string) DB::table('consultations')
                    ->where('student_complaint_id', $complaint->id)->value('priority'));
                $values = $queueValues->merge([$consultation])->filter(function ($value) {
                    return in_array($value, ['low', 'moderate', 'high', 'urgent'], true);
                })->unique();
                if ($values->count() > 1) {
                    $conflicts++;
                }
                $priority = $queueValues->first() ?: ($values->first() ?: null);
                if ($priority) {
                    DB::table('student_complaints')->where('id', $complaint->id)
                        ->update(['triage_priority' => $priority, 'updated_at' => now()]);
                    $updated++;
                }
            }
        });
        $remaining = DB::table('student_complaints')->where(function ($query) {
            $query->whereNull('triage_priority')->orWhere('triage_priority', 'unassigned');
        })->count();
        Log::info('Canonical triage priority backfill completed.', compact('updated', 'remaining', 'conflicts'));
    }

    public function down()
    {
        // Historical priorities cannot safely be distinguished from later nurse edits.
    }
}
