<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected function redirectTo()
    {
        $user = auth()->user();

        return $user && $user->isStudent()
            ? route('student.dashboard')
            : route('dashboard');
    }

    protected function sendResetResponse(Request $request, $response)
    {
        if ($request->user()) {
            $request->user()->forceFill([
                'first_login' => false,
                'must_change_password' => false,
            ])->save();
        }

        return parent::sendResetResponse($request, $response);
    }
}
