<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $account = $user->ensurePatientAccount();
        $actual = optional($account)->patient_type;
        $selected = $request->input('account_type', $actual ?: 'staff');
        if (($actual && $selected !== $actual) || (! $actual && $selected !== 'staff')) {
            Auth::logout();
            throw ValidationException::withMessages([
                'account_type' => ['The selected account type does not match this registered account.'],
            ]);
        }
        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLog::create([
            'user_id'=>$user->id,
            'action'=>'Account type selected',
            'description'=>'Authenticated through the '.($actual ?: 'staff').' account workflow.',
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
