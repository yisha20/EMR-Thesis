@extends('layouts.loginlayout')

@section('content')
<div class="student-register-shell">
    <div class="login-card-modern student-register-card">
        <div class="login-card-body">
            <div class="login-card-header">
                <p class="eyebrow">Clinic patient portal</p>
                <h1>Create your clinic account</h1>
                <span>Register as a student, faculty/employee, or authorized dependent.</span>
            </div>

            <form method="POST" action="{{ route('student.register.store') }}" class="student-register-form">
                @csrf
                @error('registration')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                <div class="student-register-grid form-fields-grid">
                    <div class="student-register-section-title">Account Information</div>
                    <div class="form-group">
                        <label for="account_type">Account Type</label>
                        <select id="account_type" name="account_type" class="form-control @error('account_type') is-invalid @enderror" required>
                            <option value="student" {{ old('account_type', 'student') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="faculty" {{ old('account_type') === 'faculty' ? 'selected' : '' }}>Faculty / Employee</option>
                            <option value="dependent" {{ old('account_type') === 'dependent' ? 'selected' : '' }}>Dependent</option>
                        </select>
                        @error('account_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="sponsor_email">Sponsor Account Email (Dependents)</label>
                        <input id="sponsor_email" type="email" name="sponsor_email" value="{{ old('sponsor_email') }}" class="form-control @error('sponsor_email') is-invalid @enderror">
                        @error('sponsor_email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="dependent_relationship">Relationship to Sponsor (Dependents)</label>
                        <input id="dependent_relationship" name="dependent_relationship" value="{{ old('dependent_relationship') }}" class="form-control @error('dependent_relationship') is-invalid @enderror">
                        @error('dependent_relationship')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="student_id_number">Student ID Number</label>
                        <input id="student_id_number" name="student_id_number" value="{{ old('student_id_number') }}" class="form-control @error('student_id_number') is-invalid @enderror">
                        @error('student_id_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="faculty_id_number">Faculty / Employee ID Number</label>
                        <input id="faculty_id_number" name="faculty_id_number" value="{{ old('faculty_id_number') }}" class="form-control @error('faculty_id_number') is-invalid @enderror">
                        @error('faculty_id_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="college_department">College / Department</label>
                        <input id="college_department" name="college_department" value="{{ old('college_department') }}" class="form-control @error('college_department') is-invalid @enderror" required>
                        @error('college_department')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" required>
                        @error('first_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" required>
                        @error('last_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="student-register-section-title">Contact &amp; Personal Details</div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input id="contact_number" name="contact_number" value="{{ old('contact_number') }}" class="form-control @error('contact_number') is-invalid @enderror" required>
                        @error('contact_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">Select gender</option>
                            @foreach (['Male', 'Female'] as $gender)
                                <option value="{{ $gender }}" {{ old('gender') === $gender ? 'selected' : '' }}>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="birth_date">Birth Date</label>
                        <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-control @error('birth_date') is-invalid @enderror">
                        @error('birth_date')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="age">Age</label>
                        <input id="age" type="number" min="0" max="150" name="age" value="{{ old('age') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="civil_status">Civil Status</label>
                        <select id="civil_status" name="civil_status" class="form-control">
                            <option value="">Select civil status</option>
                            @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $status)
                                <option value="{{ $status }}" {{ old('civil_status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="student-register-section-title">Address</div>
                    <div class="form-group">
                        <label for="home_address">Home Address</label>
                        <input id="home_address" name="home_address" value="{{ old('home_address') }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="present_address">Present Address</label>
                        <input id="present_address" name="present_address" value="{{ old('present_address') }}" class="form-control">
                    </div>

                    <div class="student-register-section-title">Account Security</div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Create Patient Account</button>
                <a href="{{ route('login') }}" class="student-auth-link">Already registered? Sign in</a>
            </form>
        </div>
    </div>
</div>
@endsection
