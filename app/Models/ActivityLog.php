<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function getActorNameAttribute()
    {
        if (!$this->user) {
            return 'Unknown User';
        }

        $name = trim($this->user->fullName());

        return $name !== '' ? $name : $this->user->email;
    }

    public function getDisplayActionAttribute()
    {
        $action = trim($this->action);
        $actorName = $this->actor_name;

        if ($actorName === 'Unknown User' || stripos($action, $actorName) === 0) {
            return $action;
        }

        return trim($actorName . ' ' . $action);
    }

    public function getActionBodyAttribute()
    {
        $action = $this->display_action;
        $actorName = $this->actor_name;

        if ($actorName !== 'Unknown User' && stripos($action, $actorName) === 0) {
            return trim(substr($action, strlen($actorName)));
        }

        return $action;
    }

    public function getActionTargetAttribute()
    {
        preg_match('/\(([^)]+)\)\s*$/', $this->action_body, $matches);

        return $matches[1] ?? null;
    }

    public function getActionTextAttribute()
    {
        if (!$this->action_target) {
            return $this->action_body;
        }

        return trim(preg_replace('/\s*\([^)]+\)\s*$/', '', $this->action_body));
    }

    public function getActionTypeAttribute()
    {
        $action = strtolower($this->action_text);

        foreach (['restored', 'archived', 'deleted', 'updated', 'added'] as $type) {
            if (strpos($action, $type) !== false) {
                return $type;
            }
        }

        return 'activity';
    }
}
