<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicQueue extends Model
{
    protected $guarded = [];
    protected $dates = ['queue_date', 'called_at', 'serving_started_at', 'completed_at'];
    public function account() { return $this->belongsTo(PatientAccount::class, 'patient_account_id'); }
    public function complaint() { return $this->belongsTo(StudentComplaint::class, 'student_complaint_id'); }
    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function logs() { return $this->hasMany(QueueStatusLog::class); }
}
