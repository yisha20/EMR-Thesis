<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClinicNotification extends Model
{
    protected $table = 'notifications';
    protected $guarded = [];
    protected $casts = ['is_read' => 'boolean'];
    protected $dates = ['read_at', 'delivered_at', 'display_until', 'acknowledged_at'];

    public function user() { return $this->belongsTo(User::class); }
    public function consultation() { return $this->belongsTo(Consultation::class, 'related_consultation_id'); }
    public function patient() { return $this->belongsTo(Patient::class, 'related_patient_id'); }
    public function complaint() { return $this->belongsTo(StudentComplaint::class, 'related_complaint_id'); }
    public function queue() { return $this->belongsTo(ClinicQueue::class, 'related_queue_id'); }

    public function scopeForUser(Builder $query, User $user)
    {
        $roleName = optional($user->role)->name;
        return $query->where(function ($target) use ($user, $roleName) {
            $target->where('user_id', $user->id)
                ->orWhere(function ($roleTarget) use ($roleName) {
                    $roleTarget->whereNull('user_id')->where('role_target', $roleName);
                });
        });
    }
}
