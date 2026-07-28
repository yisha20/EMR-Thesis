<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Models\ActivityLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:5,1')->only('sendResetLinkEmail');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            Password::sendResetLink($request->only('email'));
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Password reset requested',
                'description' => 'A password reset was requested.',
            ]);
        }
        return back()->with(
            'status',
            'If an account matches that email address, a password reset link has been sent.'
        );
    }
}
