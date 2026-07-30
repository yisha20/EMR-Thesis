@extends('layouts.app')

@section('content')
@if(session('temporary_password'))
    <div class="alert alert-warning"><strong>One-time display:</strong> Temporary credential: <code>{{ session('temporary_password') }}</code>. It expires in {{ config('auth.temporary_password_expire', 60) }} minutes.</div>
@endif
@if(auth()->user()->role->name === 'Administrator' && $user->role && !in_array($user->role->name, ['Administrator', 'Student']))
    <form method="POST" action="{{ route('users.assisted-password-reset', $user) }}" class="mb-3" onsubmit="return confirm('Generate a random temporary credential and revoke Remember Me for this staff user?')">
        @csrf
        <button type="submit" class="btn btn-warning">Issue Temporary Staff Credential</button>
    </form>
@endif
@php
  $fullName = trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name);
  $roleName = optional($user->role)->name ?? 'Unassigned';
  $roleClass = 'profile-role-badge';
  $birthDate = $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('F j, Y') : null;
  $createdAt = $user->created_at ? $user->created_at->format('F j, Y \a\t g:i A') : null;

  if ($roleName === 'Administrator') {
      $roleClass .= ' is-admin';
  } elseif ($roleName === 'Doctor') {
      $roleClass .= ' is-doctor';
  } elseif ($roleName === 'Nurse') {
      $roleClass .= ' is-nurse';
  }

  $details = [
      ['label' => 'Home Address', 'value' => $user->home_address],
      ['label' => 'Present Address', 'value' => $user->present_address],
      ['label' => 'Email Address', 'value' => $user->email],
      ['label' => 'Civil Status', 'value' => $user->civil_status],
      ['label' => 'Gender', 'value' => $user->gender],
      ['label' => 'Age', 'value' => $user->age],
      ['label' => 'Birth Date', 'value' => $birthDate],
      ['label' => 'Phone Number', 'value' => $user->phone_number],
      ['label' => 'License Number', 'value' => $user->license_number],
      ['label' => 'Created At', 'value' => $createdAt],
  ];
@endphp

<div class="profile-page-shell">
  <div class="profile-page-heading">
    <p class="eyebrow">User account</p>
    <h1>User Profile</h1>
    <span>Review staff identity, role, and contact information.</span>
  </div>

  <div class="profile-layout-grid profile-layout">
    <aside class="profile-identity-card">
      <div class="profile-card-accent"></div>
      <div class="profile-avatar-wrap">
        <img src="{{ $user->avatar ?? asset('img/no_avatar.jpg') }}" alt="{{ $fullName }}" class="profile-avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
      </div>
      <h2>{{ $fullName }}</h2>
      <span class="{{ $roleClass }}">{{ $roleName }}</span>
      <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary mt-3">Edit User</a>
    </aside>

    <section class="profile-details-card">
      <div class="profile-details-header">
        <div>
          <p class="eyebrow">Account details</p>
          <h2>Profile Information</h2>
        </div>
      </div>

      <div class="profile-data-grid">
        @foreach ($details as $detail)
          <div class="profile-data-item">
            <span>{{ strtoupper($detail['label']) }}</span>
            <strong>{{ filled($detail['value']) ? $detail['value'] : 'Not Provided' }}</strong>
          </div>
        @endforeach
      </div>
    </section>
  </div>
</div>
@endsection
