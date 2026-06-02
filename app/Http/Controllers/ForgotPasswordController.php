<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPasswordMail;
use App\Mail\GeneratedPassword;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Send forgot-password token to given email. 
     */
    public function send()
    {
        request()->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $email = request()->get('email');
        
        $user = User::whereEmail($email)->first();
        
        Mail::to($email)->later(
            now()->addSeconds(15), new ForgotPasswordMail($user)
        );

        return redirect()
            ->back()
            ->with('success', 'The email will be sent to you for confirmation shortly.');
    }

    /**
     * Verifies the token.
     * 
     * @param String $code
     * @return Void
     */
    public function verify($email)
    {
        $generatedPassword = Str::random(16);

        $user = User::whereEmail($email)->first();

        $user->update([
            'password' => bcrypt($generatedPassword)
        ]);

        Mail::to($email)->later(
            now()->addSeconds(15), new GeneratedPassword($user, $generatedPassword)
        );
        
        return redirect(route('login'))->with('success', 'A generated password was sent to your email address.');
    }
}
