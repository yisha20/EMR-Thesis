<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        return route('login');
    }

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => null,
            'first_login' => false,
            'must_change_password' => false,
            'temporary_password_expires_at' => null,
        ])->save();
        if (Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Password successfully reset',
            'description' => 'Password reset completed; persistent login token invalidated.',
        ]);
        event(new PasswordReset($user));
    }

    protected function sendResetResponse(Request $request, $response)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', trans($response));
    }
}
