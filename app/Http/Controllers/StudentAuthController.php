<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Role;
use App\Student;
use App\User;
use App\PatientAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class StudentAuthController extends Controller
{
    private const ACCOUNT_TYPES = ['student', 'faculty', 'dependent'];
    private const RELATIONSHIPS = ['Spouse', 'Child', 'Parent', 'Sibling', 'Legal dependent', 'Other approved family dependent'];

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showAccountTypeSelection()
    {
        return view('student.auth.account-type');
    }

    public function showRegistrationForm(Request $request)
    {
        $accountType = $request->query('type');
        if ($accountType === null) {
            return redirect()->route('patient.register.type');
        }
        abort_unless(in_array($accountType, self::ACCOUNT_TYPES, true), 404);

        return view('student.auth.register', [
            'accountType' => $accountType,
            'relationships' => self::RELATIONSHIPS,
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'account_type' => 'required|in:student,faculty,dependent',
            'student_id_number' => 'required_if:account_type,student|nullable|string|max:50|unique:students,student_id_number|unique:patient_accounts,student_id_number',
            'faculty_id_number' => 'required_if:account_type,faculty|nullable|string|max:50|unique:patient_accounts,faculty_id_number',
            'sponsor_type' => 'required_if:account_type,dependent|nullable|in:faculty',
            'sponsor_id_number' => 'required_if:account_type,dependent|nullable|string|max:50',
            'sponsor_email' => 'required_if:account_type,dependent|nullable|email|max:255',
            'dependent_relationship' => 'required_if:account_type,dependent|nullable|in:'.implode(',', self::RELATIONSHIPS),
            'dependent_relationship_details' => 'required_if:dependent_relationship,Other approved family dependent|nullable|string|max:100',
            'verification_consent' => $request->input('account_type') === 'dependent'
                ? 'required|accepted'
                : 'nullable',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'suffix' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'college_department' => 'required_unless:account_type,dependent|nullable|string|max:255',
            'program' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:50',
            'position_designation' => 'required_if:account_type,faculty|nullable|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'contact_number' => 'required_unless:account_type,dependent|nullable|string|max:50',
            'gender' => 'required|in:Male,Female',
            'birth_date' => 'required|date|before_or_equal:today',
            'civil_status' => 'required|string|max:50',
            'home_address' => 'required|string|max:255',
            'present_address' => 'required|string|max:255',
        ], [
            'email.unique' => 'An account already exists for this email address.',
            'student_id_number.unique' => 'An account already exists for this IIT ID number.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            DB::transaction(function () use ($data) {
                // All self-registrations receive the non-staff Patient role.
                // Account type remains separate in patient_accounts.
                $patientRole = Role::whereRaw('LOWER(name) = ?', ['patient'])->firstOrFail();
                $identifier = $data['account_type'] === 'student'
                    ? $data['student_id_number']
                    : ($data['account_type'] === 'faculty'
                        ? $data['faculty_id_number']
                        : 'DEP-'.strtoupper(substr(sha1($data['email']), 0, 16)));
                $sponsor = null;
                if ($data['account_type'] === 'dependent') {
                    $sponsor = PatientAccount::whereHas('user', function ($query) use ($data) {
                        $query->where('email', $data['sponsor_email'])->where('status', 'Active');
                    })->where('patient_type', 'faculty')
                        ->where('faculty_id_number', $data['sponsor_id_number'])
                        ->where('verification_status', 'verified')->first();
                    if (! $sponsor) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'sponsor_id_number' => 'The sponsor details could not be verified.',
                        ]);
                    }
                }
                $age = now()->diffInYears(\Illuminate\Support\Carbon::parse($data['birth_date']));
                $fullName = trim(implode(' ', array_filter([
                    $data['first_name'],
                    $data['middle_name'] ?? null,
                    $data['last_name'],
                ])));

                $user = User::create([
                    'role_id' => $patientRole->id,
                    'username' => $identifier,
                    'name' => $fullName,
                    'status' => 'Active',
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['contact_number'],
                    'gender' => $data['gender'] ?? null,
                    'age' => $age,
                    'birthdate' => $data['birth_date'] ?? null,
                    'civil_status' => $data['civil_status'] ?? null,
                    'home_address' => $data['home_address'] ?? null,
                    'present_address' => $data['present_address'] ?? null,
                    'first_login' => false,
                    'must_change_password' => false,
                ]);

                Student::create([
                    'user_id' => $user->id,
                    'student_id_number' => $identifier,
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'college_department' => $data['college_department'] ?? 'Not applicable',
                    'contact_number' => $data['contact_number'] ?? 'Not provided',
                    'gender' => $data['gender'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'age' => $age,
                    'civil_status' => $data['civil_status'] ?? null,
                    'home_address' => $data['home_address'] ?? null,
                    'present_address' => $data['present_address'] ?? null,
                ]);

                PatientAccount::create([
                    'user_id' => $user->id,
                    'patient_type' => $data['account_type'],
                    'student_id_number' => $data['account_type'] === 'student' ? $identifier : null,
                    'faculty_id_number' => $data['account_type'] === 'faculty' ? $identifier : null,
                    'suffix' => $data['suffix'] ?? null,
                    'college_department' => $data['college_department'] ?? null,
                    'program' => $data['program'] ?? null,
                    'year_level' => $data['year_level'] ?? null,
                    'position_designation' => $data['position_designation'] ?? null,
                    'employment_type' => $data['employment_type'] ?? null,
                    'sponsor_patient_account_id' => optional($sponsor)->id,
                    'sponsor_type' => $data['sponsor_type'] ?? null,
                    'sponsor_id_number' => $data['sponsor_id_number'] ?? null,
                    'sponsor_email' => $data['sponsor_email'] ?? null,
                    'dependent_relationship' => $data['dependent_relationship'] ?? null,
                    'dependent_relationship_details' => $data['dependent_relationship_details'] ?? null,
                    'verification_consent' => (bool) ($data['verification_consent'] ?? false),
                    'verification_status' => $data['account_type'] === 'dependent' ? 'pending_verification' : 'verified',
                    'health_assessment_status' => 'not_started',
                ]);

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => ucfirst($data['account_type']) . ' registered account: ' . $fullName,
                    'description' => 'Patient portal self-registration completed.',
                ]);
            });
        } catch (Throwable $exception) {
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                throw $exception;
            }
            report($exception);

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['registration' => 'Unable to create the patient portal account. Please review the details and try again.']);
        }

        return redirect()->route('login')
            ->with('success', 'Your patient portal account was created. Sign in to complete your health assessment.');
    }
}
