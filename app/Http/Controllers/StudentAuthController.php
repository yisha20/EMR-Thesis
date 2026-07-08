<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Role;
use App\Student;
use App\User;
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
        $data = $request->validate([
            'student_id_number' => 'required|string|max:50|unique:students,student_id_number',
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
                $fullName = trim(implode(' ', array_filter([
                    $data['first_name'],
                    $data['middle_name'] ?? null,
                    $data['last_name'],
                ])));

                $user = User::create([
                    'role_id' => $studentRole->id,
                    'username' => $data['student_id_number'],
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
                    'student_id_number' => $data['student_id_number'],
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

                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'Student registered account: ' . $fullName,
                    'description' => 'Student self-registration completed.',
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['registration' => 'Unable to create the student account. Please try again.']);
        }

        return redirect()->route('login')
            ->with('success', 'Your student account was created. You may now sign in.');
    }
}
