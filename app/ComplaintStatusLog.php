<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplaintStatusLog extends Model
{
    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(StudentComplaint::class, 'student_complaint_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
