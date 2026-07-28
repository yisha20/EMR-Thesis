<?php

namespace App;

use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Rainwater\Active\Active;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $dates = [
        'deleted_at',
        'last_activity',
        'last_login_at',
    ];

    protected $with = [
        'role'
    ];

    public function fullName()
    {
        return "$this->first_name $this->middle_name $this->last_name";
    }

    public function getAvatarAttribute($value)
    {
        if (! $value) {
            return null;
        }

        $path = parse_url($value, PHP_URL_PATH);

        if ($path && strpos($path, '/storage/') === 0) {
            return $path;
        }

        return $value;
    }
    
    public function license()
    {
        return "$this->license_number";
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function addedPatients()
    {
    	return $this->hasMany('App\Patient', 'auth_by');
    }

    public function updatedPatients()
    {
    	return $this->hasMany('App\Patient', 'updated_by');
    }

    public function getRole()
    {
        return $this->role->name;
    }

    public function getAddedPatients()
    {
        return $this->addedPatients;
    }

    public function getUpdatedPatients()
    {
        return $this->updatedPatients;
    }

    protected function getActive()
    {
        return Active::users()->get()->map(function ($data) {
            return $data->user;
        });
    }

    public function services()
    {
        return $this->hasMany('App\Service', 'added_by');
    }

    public function archivedBy()
    {
        return $this->belongsTo(self::class, 'archived_by');
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function patientAccount()
    {
        return $this->hasOne(PatientAccount::class);
    }

    public function doctorProfile()
    {
        return $this->hasOne(DoctorProfile::class);
    }

    public function doctorConsultations()
    {
        return $this->hasMany(Consultation::class, 'doctor_id');
    }

    public function isPatientPortalUser()
    {
        return $this->patientAccount()->exists() || $this->isStudent();
    }

    public function ensurePatientAccount()
    {
        $existing = $this->patientAccount()->first();
        if ($existing) {
            $this->setRelation('patientAccount', $existing);
            return $existing;
        }
        $student = $this->student()->first();
        if (! $this->isStudent() || ! $student) {
            return null;
        }
        $account = $this->patientAccount()->firstOrCreate(['user_id'=>$this->id], [
            'patient_id' => Patient::where('id_number', $student->student_id_number)->value('id'),
            'patient_type' => 'student',
            'student_id_number' => $student->student_id_number,
            'verification_status' => 'verified',
            'health_assessment_status' => 'patient_submitted',
            'health_assessment_completed_at' => now(),
        ]);
        $this->setRelation('patientAccount', $account);
        return $account;
    }

    public function isStudent()
    {
        return $this->role && strcasecmp($this->role->name, 'Student') === 0;
    }

    public function isActive()
    {
        return strcasecmp($this->status ?: 'Active', 'Active') === 0;
    }
}
