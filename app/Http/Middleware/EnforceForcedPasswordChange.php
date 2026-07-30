<?php

namespace App\Http\Middleware;

use Closure;

class EnforceForcedPasswordChange
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            return $next($request);
        }

        $isStaff = ! in_array($user->role->name, ['Administrator', 'Student'], true);
        $mustChange = (bool) ($user->must_change_password || $user->first_login);
        $allowed = $request->routeIs('password.change', 'password.change.update', 'logout');

        if ($isStaff && $mustChange && ! $allowed) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
