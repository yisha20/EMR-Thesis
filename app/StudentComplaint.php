<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StudentComplaint extends Model
{
    protected $guarded = [];

    protected $dates = [
        'submitted_at',
        'reviewed_at',
        'consultation_started_at',
        'completed_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ComplaintStatusLog::class)->latest();
    }

    public function counterService()
    {
        return $this->hasOne(CounterService::class);
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }
}
