<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function redirectTo()
{
    $user = Auth::user();

    // Force password change on first login for non-admins
    if ($user->first_login && $user->role->name !== 'Administrator') {
        return route('password.change');
    }

    return '/dashboard';
}

}
