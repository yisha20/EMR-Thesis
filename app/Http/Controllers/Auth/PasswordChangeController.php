<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordChangeController extends Controller
{
    // ✅ Match the route name: showChangePasswordForm
    public function showChangePasswordForm()
    {
        return view('auth.change_password');
    }

    // ✅ Handle password change submission
    public function updatePassword(Request $request)
{
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = Auth::user();
    $user->password = Hash::make($request->password);
    $user->first_login = false; // mark as changed
    $user->save();

    Auth::logout(); // ✅ End current session to prevent CSRF issues

    return redirect('/login')->with('success', 'Password changed successfully. Please log in with your new password.');
}

}
