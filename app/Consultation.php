<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $guarded = [];

    protected $dates = ['forwarded_at', 'called_at', 'started_at', 'completed_at', 'follow_up_date'];

    public function complaint() { return $this->belongsTo(StudentComplaint::class, 'student_complaint_id'); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function forwarder() { return $this->belongsTo(User::class, 'forwarded_by'); }
    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function medicalRecord() { return $this->hasOne(MedicalRecord::class); }
    public function prescription() { return $this->hasOne(Prescription::class); }
    public function caller() { return $this->belongsTo(User::class, 'called_by'); }
    public function notifications() { return $this->hasMany(ClinicNotification::class, 'related_consultation_id'); }
}
