<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClinicQueue extends Model
{
    protected $guarded = [];
    protected $dates = ['queue_date', 'called_at', 'serving_started_at', 'completed_at', 'missed_at',
        'away_at', 'returning_at', 'present_at', 'nearly_next_notified_at', 'next_notified_at',
        'called_notification_sent_at', 'patient_acknowledged_at', 'last_recalled_at'];
    public function account() { return $this->belongsTo(PatientAccount::class, 'patient_account_id'); }
    public function complaint() { return $this->belongsTo(StudentComplaint::class, 'student_complaint_id'); }
    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function logs() { return $this->hasMany(QueueStatusLog::class); }
    public function nurse() { return $this->belongsTo(User::class, 'assigned_nurse_id'); }
    public function doctor() { return $this->belongsTo(User::class, 'assigned_doctor_id'); }
}
