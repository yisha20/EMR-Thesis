<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientAccount extends Model
{
    protected $guarded = [];
    protected $dates = ['health_assessment_completed_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function sponsor() { return $this->belongsTo(self::class, 'sponsor_patient_account_id'); }
    public function sponsoredAccounts() { return $this->hasMany(self::class, 'sponsor_patient_account_id'); }
    public function dependents() { return $this->hasMany(PatientDependent::class, 'sponsor_patient_account_id'); }
    public function assessments() { return $this->hasMany(HealthAssessment::class); }
    public function queues() { return $this->hasMany(ClinicQueue::class); }
    public function complaints() { return $this->hasMany(StudentComplaint::class); }

    public function latestAssessment()
    {
        return $this->hasOne(HealthAssessment::class)->orderByDesc('version');
    }

    public function assessmentAllowsDashboard()
    {
        return in_array($this->health_assessment_status, ['patient_submitted', 'under_review', 'clinically_completed'], true);
    }

    public function getIdentifierAttribute()
    {
        return $this->student_id_number ?: $this->faculty_id_number;
    }

    public function accessibleAccountIds()
    {
        return collect([$this->id])
            ->merge($this->dependents()->whereNotNull('patient_account_id')->pluck('patient_account_id'))
            ->merge($this->sponsoredAccounts()->pluck('id'))
            ->filter()->unique()->values();
    }
}
