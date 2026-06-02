<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use SoftDeletes;
    
    /**
     * The attributes that aren't mass assignable.
     * 
     * @var Array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Cast attribute to data-type.
     * 
     * @var Array
     */
    protected $casts = [
        'vital_signs' => 'json'
    ];

    /**
     * A medical record belongs to a patient.
     * 
     * @return BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo('App\Patient');
    }

    /**
     * A medical record belongs to a service.
     * 
     * @return BelongsTo
     */
    public function service()
    {
        return $this->belongsTo('App\Service');
    }

    /**
     * Get the consultation date and time in a readable format.
     * 
     * @return String
     */
    public function getDateTimeConsultation()
    {
        return Carbon::parse("$this->date_of_consultation $this->time_of_consultation")->format('Y-m-d h:m A');
    }
}
