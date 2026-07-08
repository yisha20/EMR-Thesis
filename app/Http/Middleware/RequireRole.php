<?php

namespace App\Http\Middleware;

use Closure;

class RequireRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->role || !in_array($user->role->name, $roles, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
