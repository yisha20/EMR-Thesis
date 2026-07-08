<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $guarded = [];

    protected $casts = ['medications' => 'array'];
    protected $dates = ['follow_up_date'];

    public function consultation() { return $this->belongsTo(Consultation::class); }
    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(User::class, 'doctor_id'); }
    public function medicalRecord() { return $this->hasOne(MedicalRecord::class); }

    public function getSummaryAttribute()
    {
        $medications = collect($this->medications ?: [])->pluck('medication')->filter()->implode(', ');
        return $medications !== '' ? $medications : $this->prescription_type;
    }
}
