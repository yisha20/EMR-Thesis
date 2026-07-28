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
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('student.auth.register');
    }

    public function register(Request $request)
    {
        $request->merge(['account_type' => $request->input('account_type', 'student')]);
        $data = $request->validate([
            'account_type' => 'required|in:student,faculty,dependent',
            'student_id_number' => 'required_if:account_type,student|nullable|string|max:50|unique:students,student_id_number|unique:patient_accounts,student_id_number',
            'faculty_id_number' => 'required_if:account_type,faculty|nullable|string|max:50|unique:patient_accounts,faculty_id_number',
            'sponsor_email' => 'required_if:account_type,dependent|nullable|email|max:255',
            'dependent_relationship' => 'required_if:account_type,dependent|nullable|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'college_department' => 'required|string|max:255',
            'contact_number' => 'required|string|max:50',
            'gender' => 'nullable|in:Male,Female',
            'age' => 'nullable|integer|min:0|max:150',
            'birth_date' => 'nullable|date',
            'civil_status' => 'nullable|string|max:50',
            'home_address' => 'nullable|string|max:255',
            'present_address' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'An account already exists for this email address.',
            'student_id_number.unique' => 'An account already exists for this IIT ID number.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        try {
            DB::transaction(function () use ($data) {
                $studentRole = Role::whereRaw('LOWER(name) = ?', ['student'])->firstOrFail();
                $identifier = $data['account_type'] === 'student'
                    ? $data['student_id_number']
                    : ($data['account_type'] === 'faculty'
                        ? $data['faculty_id_number']
                        : 'DEP-'.strtoupper(substr(sha1($data['email']), 0, 16)));
                $sponsor = null;
                if ($data['account_type'] === 'dependent') {
                    $sponsor = PatientAccount::whereHas('user', function ($query) use ($data) {
                        $query->where('email', $data['sponsor_email'])->where('status', 'Active');
                    })->where('verification_status', 'verified')->firstOrFail();
                }
                $fullName = trim(implode(' ', array_filter([
                    $data['first_name'],
                    $data['middle_name'] ?? null,
                    $data['last_name'],
                ])));

                $user = User::create([
                    'role_id' => $studentRole->id,
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
                    'age' => $data['age'] ?? null,
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
                    'college_department' => $data['college_department'],
                    'contact_number' => $data['contact_number'],
                    'gender' => $data['gender'] ?? null,
                    'birth_date' => $data['birth_date'] ?? null,
                    'age' => $data['age'] ?? null,
                    'civil_status' => $data['civil_status'] ?? null,
                    'home_address' => $data['home_address'] ?? null,
                    'present_address' => $data['present_address'] ?? null,
                ]);

                PatientAccount::create([
                    'user_id' => $user->id,
                    'patient_type' => $data['account_type'],
                    'student_id_number' => $data['account_type'] === 'student' ? $identifier : null,
                    'faculty_id_number' => $data['account_type'] === 'faculty' ? $identifier : null,
                    'sponsor_patient_account_id' => optional($sponsor)->id,
                    'dependent_relationship' => $data['dependent_relationship'] ?? null,
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
            report($exception);

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['registration' => 'Unable to create the student account. Please try again.']);
        }

        return redirect()->route('login')
            ->with('success', 'Your patient portal account was created. Sign in to complete your health assessment.');
    }
}
