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
        'vital_signs' => 'json',
        'date_of_consultation' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
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

    public function studentComplaint()
    {
        return $this->belongsTo(StudentComplaint::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attendingStaff()
    {
        return $this->belongsTo(User::class, 'attending_staff_id');
    }

    public function counterService()
    {
        return $this->belongsTo(CounterService::class);
    }

    public function consultation()
    {
        return $this->belongsTo(Consultation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the consultation date and time in a readable format.
     * 
     * @return String
     */
    public function getDateTimeConsultation()
    {
        if (!$this->date_of_consultation) {
            return 'Not set';
        }

        $dateTime = $this->date_of_consultation instanceof \DateTimeInterface
            ? Carbon::instance($this->date_of_consultation)->copy()->startOfDay()
            : Carbon::parse($this->date_of_consultation)->startOfDay();

        if ($this->time_of_consultation) {
            $time = Carbon::parse($this->time_of_consultation);
            $dateTime->setTime($time->hour, $time->minute, $time->second);
        }

        return $dateTime->format('Y-m-d h:i A');
    }
}
