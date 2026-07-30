<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PasswordChangeController extends Controller
{
    public function showChangePasswordForm()
    {
        return view('auth.change_password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->first_login = false;
        $user->must_change_password = false;
        $user->remember_token = null;
        $user->temporary_password_expires_at = null;
        $user->save();
        if (Schema::hasTable(config('session.table', 'sessions'))) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'Forced password change completed',
            'description' => 'Permanent password created; forced-change state and persistent login token cleared.',
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Password changed successfully. Please log in with your new password.');
    }
}
