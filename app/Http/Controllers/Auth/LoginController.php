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

        // Force password change on first login for non-admins.
        if ($user->first_login && $user->role->name !== 'Administrator') {
            return route('password.change');
        }

        return route('dashboard');
    }
}
