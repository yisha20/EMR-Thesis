<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    
    protected $guarded = [];

    protected $with = [
        'addedBy'
    ];
    
    public function addedBy()
    {
        return $this->belongsTo('App\User', 'added_by');
    }

    public function archivedBy()
    {
        return $this->belongsTo('App\User', 'archived_by');
    }
}
