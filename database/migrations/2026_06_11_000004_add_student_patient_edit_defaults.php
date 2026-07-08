<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddStudentPatientEditDefaults extends Migration
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
                $pastMedicalHistory = array_merge([
                    'pastmedical_history' => [],
                    'last_menstrual_period' => '',
                    'menstrual_pattern' => '',
                ], $this->normalizeObject($record->past_medical_history));
                $familyHistory = array_merge([
                    'family_history' => [],
                ], $this->normalizeObject($record->family_history));
                $socialHistory = array_merge([
                    'is_smoking' => '',
                    'packs_smoked' => '',
                    'is_drinking_beer' => '',
                    'drinking_frequency' => '',
                    'is_taking_medication' => '',
                    'medications' => [],
                    'allergies' => [],
                    'exercise' => '',
                    'diet' => '',
                ], $this->normalizeObject($record->social_history));
                $vitalSigns = array_merge([
                    'temperature' => '',
                    'pulse_rate' => '',
                    'respiratory_rate' => '',
                    'blood_pressure' => '',
                    'weight' => '',
                ], $this->normalizeObject($record->vital_signs));
                $assessment = array_merge([
                    'physically_fit' => '',
                    'physically_fit_description' => '',
                    'date_examined' => '',
                    'by' => '',
                    'license_no' => '',
                ], $this->normalizeObject($record->assessment));
                $nursingInterventions = $this->normalizeObject($record->nursing_interventions);

                $pastMedicalHistory['pastmedical_history'] = $this->normalizeList($pastMedicalHistory['pastmedical_history']);
                $familyHistory['family_history'] = $this->normalizeList($familyHistory['family_history']);
                $socialHistory['medications'] = $this->normalizeList($socialHistory['medications']);
                $socialHistory['allergies'] = $this->normalizeList($socialHistory['allergies']);
                $nursingInterventions['nursing_interventions'] = array_map(
                    function ($intervention) {
                        if (!is_array($intervention)) {
                            $intervention = ['intervention' => $intervention];
                        }

                        return array_merge([
                            'intervention' => '',
                            'time' => '',
                            'by' => '',
                        ], $intervention);
                    },
                    $this->normalizeList($nursingInterventions['nursing_interventions'] ?? [])
                );

                DB::table('health_examination_records')
                    ->where('id', $record->id)
                    ->update([
                        'past_medical_history' => json_encode($pastMedicalHistory),
                        'family_history' => json_encode($familyHistory),
                        'social_history' => json_encode($socialHistory),
                        'vital_signs' => json_encode($vitalSigns),
                        'assessment' => json_encode($assessment),
                        'nursing_interventions' => json_encode($nursingInterventions),
                    ]);
            });
    }

    public function down()
    {
        // Defaults only fill missing keys, so this repair is intentionally irreversible.
    }

    private function normalizeObject($value)
    {
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return is_array($decoded) ? $decoded : [$decoded];
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
