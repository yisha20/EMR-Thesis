<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\ActivityLog;
use App\User;

class ForgotPasswordController extends Controller
{
    /**
     * Send forgot-password token to given email. 
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            Password::sendResetLink($request->only('email'));
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Password reset requested',
                'description' => 'A password reset was requested.',
            ]);
        }

        return redirect()->back()->with(
            'success',
            'If an account matches that email address, a password reset link has been sent.'
        );
    }

    /**
     * Verifies the token.
     * 
     * @param String $code
     * @return Void
     */
    public function verify($email)
    {
        abort(404);
    }
}
