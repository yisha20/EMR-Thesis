<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MedicalCertificate extends Model
{
    protected $guarded = [];
    protected $dates = ['issue_date', 'valid_from', 'valid_until', 'issued_at', 'cancelled_at'];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function doctor() { return $this->belongsTo(User::class, 'issued_by_doctor_id'); }
    public function replacedCertificate() { return $this->belongsTo(self::class, 'replaces_certificate_id'); }
    public function replacement() { return $this->hasOne(self::class, 'replaces_certificate_id'); }

    public function getPurposeLabelAttribute()
    {
        return [
            'ojt' => 'OJT',
            'scholarship_application' => 'Scholarship Application',
            'employment' => 'Employment',
            'school_requirement' => 'School Requirement',
            'sports_activity' => 'Sports Participation',
            'return_to_school' => 'Return to School',
            'travel_requirement' => 'Travel Requirement',
            'other' => 'Other',
        ][$this->purpose] ?? 'Other';
    }

    public function getFitnessLabelAttribute()
    {
        return [
            'physically_fit' => 'Physically Fit',
            'physically_unfit' => 'Physically Unfit',
            'fit_with_restrictions' => 'Fit with Restrictions',
            'not_assessed' => 'Other',
            'other' => 'Other',
        ][$this->fitness_status] ?? 'Other';
    }
}
