<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->temporary_password_expires_at
            && $user->temporary_password_expires_at->isPast()
            && ($user->must_change_password || $user->first_login)
            && ! $user->isPatientPortalUser()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors([
                'email' => 'The temporary credential has expired. Contact an authorized administrator.',
            ]);
        }
        $user->ensurePatientAccount();
        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLog::create([
            'user_id'=>$user->id,
            'action'=>'User logged in',
            'description'=>'Authenticated through the unified login.',
        ]);
    }

    protected function credentials(Request $request)
    {
        return [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'status' => 'Active',
        ];
    }

    protected function redirectTo()
    {
        $user = Auth::user();

        if ($user->isPatientPortalUser()) {
            return optional($user->patientAccount)->assessmentAllowsDashboard()
                ? route('student.dashboard')
                : route('patient.assessment.edit');
        }

        // Reuse the existing forced-change flags for staff only.
        if (($user->first_login || $user->must_change_password)
            && ! in_array($user->role->name, ['Administrator', 'Student'], true)) {
            return route('password.change');
        }

        return route('dashboard');
    }
}
