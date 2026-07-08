<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $description = null)
    {
        $user = Auth::user();
        $action = trim($action);

        if ($user) {
            $actorName = trim($user->fullName());
            $actorName = $actorName !== '' ? $actorName : $user->email;

            if (stripos($action, $actorName) !== 0) {
                $action = trim($actorName . ' ' . $action);
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}
