<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CounterService extends Model
{
    protected $guarded = [];

    protected $dates = ['handled_at'];

    public function complaint() { return $this->belongsTo(StudentComplaint::class, 'student_complaint_id'); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function handler() { return $this->belongsTo(User::class, 'handled_by'); }
    public function medicalRecord() { return $this->hasOne(MedicalRecord::class); }
}
