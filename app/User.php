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
    ];

    protected $with = [
        'role'
    ];

    public function fullName()
    {
        return "$this->first_name $this->middle_name $this->last_name";
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
}
