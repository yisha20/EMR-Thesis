<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Send forgot-password token to given email. 
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => trans($status)]);
        }

        return redirect()->back()->with('success', trans($status));
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
