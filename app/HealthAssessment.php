<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthAssessment extends Model
{
    protected $guarded = [];
    protected $casts = [
        'personal_information' => 'array', 'womens_health' => 'array',
        'social_history' => 'array', 'physical_examination' => 'array',
        'vital_signs' => 'array', 'clinical_assessment' => 'array',
    ];
    protected $dates = ['submitted_at', 'reviewed_at', 'clinically_completed_at'];

    public function account() { return $this->belongsTo(PatientAccount::class, 'patient_account_id'); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function medicalHistories() { return $this->hasMany(HealthAssessmentMedicalHistory::class); }
    public function familyHistories() { return $this->hasMany(HealthAssessmentFamilyHistory::class); }
    public function medications() { return $this->hasMany(HealthAssessmentMedication::class)->orderBy('display_order'); }
    public function nursingInterventions() { return $this->hasMany(HealthAssessmentNursingIntervention::class); }
}
