<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthExaminationRecord extends Model
{
    use SoftDeletes;
    
    /**
     * The fields that are not mass-asssignable.
     * 
     * @var Array
     */
    protected $guarded = [
        'created_at',
        'updated_at'
    ];

    /**
     * The fields get casted to a custom data-type.
     * 
     * @var Array
     */
    protected $casts = [
        'past_medical_history' => 'array',
        'family_history' => 'array',
        'social_history' => 'array',
        'phyiscal_examination' => 'array',
        'vital_signs' => 'array',
        'assessment' => 'array',
        'nursing_interventions' => 'array'
    ];

    /**
     * This health examination belongs to a Patient.
     * 
     * @return BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo('App\Patient');
    }
}
