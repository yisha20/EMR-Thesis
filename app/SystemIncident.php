<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SystemIncident extends Model
{
    protected $guarded = [];
    protected $dates = ['detected_at', 'resolved_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function resolver() { return $this->belongsTo(User::class, 'resolved_by'); }
}
