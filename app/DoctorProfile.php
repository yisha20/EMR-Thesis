<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    protected $guarded = [];
    protected $casts = ['template_settings' => 'array'];
    protected $dates = ['signature_uploaded_at', 'signature_verified_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function verifier() { return $this->belongsTo(User::class, 'signature_verified_by'); }
}
