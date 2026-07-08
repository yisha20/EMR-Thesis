<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RepairStudentPatientHealthHistory extends Migration
{
    public function up()
    {
        DB::table('health_examination_records')
            ->join('patients', 'patients.id', '=', 'health_examination_records.patient_id')
            ->where('patients.type', 'Student')
            ->select('health_examination_records.*')
            ->orderBy('health_examination_records.id')
            ->get()
            ->each(function ($record) {
                $pastMedicalHistory = $this->normalizeObject($record->past_medical_history);
                $familyHistory = $this->normalizeObject($record->family_history);
                $socialHistory = $this->normalizeObject($record->social_history);
                $nursingInterventions = $this->normalizeObject($record->nursing_interventions);

                $pastMedicalHistory['pastmedical_history'] = $this->normalizeList($pastMedicalHistory['pastmedical_history'] ?? []);
                $familyHistory['family_history'] = $this->normalizeList($familyHistory['family_history'] ?? []);
                $socialHistory['medications'] = $this->normalizeList($socialHistory['medications'] ?? []);
                $socialHistory['allergies'] = $this->normalizeList($socialHistory['allergies'] ?? []);
                $nursingInterventions['nursing_interventions'] = $this->normalizeList(
                    $nursingInterventions['nursing_interventions'] ?? []
                );

                DB::table('health_examination_records')
                    ->where('id', $record->id)
                    ->update([
                        'past_medical_history' => json_encode($pastMedicalHistory),
                        'family_history' => json_encode($familyHistory),
                        'social_history' => json_encode($socialHistory),
                        'phyiscal_examination' => json_encode($this->normalizeObject($record->phyiscal_examination)),
                        'vital_signs' => json_encode($this->normalizeObject($record->vital_signs)),
                        'assessment' => json_encode($this->normalizeObject($record->assessment)),
                        'nursing_interventions' => json_encode($nursingInterventions),
                    ]);
            });
    }

    public function down()
    {
        // The repair only normalizes malformed values and is intentionally irreversible.
    }

    private function normalizeObject($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            return $decoded;
        }

        return [$decoded];
    }

    private function normalizeList($value)
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value) && strpos($value, ',') !== false) {
            return array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
        }

        return [$value];
    }
}
