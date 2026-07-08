@extends('layouts.app')

@section('content')
@php
  $fullName = trim($user->first_name . ' ' . $user->middle_name . ' ' . $user->last_name);
  $roleName = optional($user->role)->name ?? 'Unassigned';
  $roleClass = 'profile-role-badge';
  $birthDate = $user->birthdate ? \Carbon\Carbon::parse($user->birthdate)->format('F j, Y') : null;

  if ($roleName === 'Administrator') {
      $roleClass .= ' is-admin';
  } elseif ($roleName === 'Doctor') {
      $roleClass .= ' is-doctor';
  } elseif ($roleName === 'Nurse') {
      $roleClass .= ' is-nurse';
  }

  $displayValue = function ($value) {
      $value = trim((string) $value);
      return $value !== '' ? e($value) : '<em>Not Provided</em>';
  };

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
  ];
@endphp

<div class="profile-page-shell">
  <div class="profile-page-heading">
    <p class="eyebrow">User profile</p>
    <h1>Account Overview</h1>
    <span>Review clinic account identity, role, and contact information.</span>
  </div>

  <div class="profile-layout-grid profile-layout">
    <aside class="profile-identity-card">
      <div class="profile-card-accent"></div>
      <div class="profile-avatar-wrap">
        <img src="{{ $user->avatar ?? asset('img/no_avatar.jpg') }}" alt="{{ $fullName }}" class="profile-avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
      </div>
      <h2>{{ $fullName }}</h2>
      <span class="{{ $roleClass }}">{{ $roleName }}</span>
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
            <strong>{!! $displayValue($detail['value']) !!}</strong>
          </div>
        @endforeach
      </div>

      <div class="profile-info-banner">
        <i class="fa fa-info-circle"></i>
        <span>For Profile Updates, Please Go to the EMR System Administrator.</span>
      </div>
    </section>
  </div>
</div>
@stop
