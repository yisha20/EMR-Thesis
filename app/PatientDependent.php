<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PatientDependent extends Model
{
    protected $guarded = [];
    protected $dates = ['birth_date', 'verified_at'];

    public function sponsor() { return $this->belongsTo(PatientAccount::class, 'sponsor_patient_account_id'); }
    public function patientAccount() { return $this->belongsTo(PatientAccount::class); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
}
