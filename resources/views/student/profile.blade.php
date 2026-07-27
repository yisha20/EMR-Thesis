@extends('layouts.app')

@section('content')
@php
    $displayValue = function ($value) {
        $value = trim((string) $value);
        return $value !== '' ? e($value) : '<em>Not Provided</em>';
    };

    $details = [
        ['label' => ucfirst($account->patient_type) . ' ID Number', 'value' => $account->identifier ?: $student->student_id_number],
        ['label' => 'College / Department', 'value' => $student->college_department],
        ['label' => 'Email Address', 'value' => $student->email ?: $student->user->email],
        ['label' => 'Contact Number', 'value' => $student->contact_number],
        ['label' => 'Gender', 'value' => $student->gender],
        ['label' => 'Age', 'value' => $student->age],
        ['label' => 'Birth Date', 'value' => $student->birth_date ? $student->birth_date->format('F j, Y') : null],
        ['label' => 'Civil Status', 'value' => $student->civil_status],
        ['label' => 'Home Address', 'value' => $student->home_address],
        ['label' => 'Present Address', 'value' => $student->present_address],
    ];
@endphp

<div class="profile-page-shell">
    <div class="profile-page-heading">
        <p class="eyebrow">Patient profile</p>
        <h1>Account Overview</h1>
        <span>Review your identity, department, and contact information.</span>
    </div>

    <div class="profile-layout-grid profile-layout">
        <aside class="profile-identity-card">
            <div class="profile-card-accent"></div>
            <div class="profile-avatar-wrap">
                <img src="{{ $student->user->avatar ?? asset('img/no_avatar.jpg') }}" alt="{{ $student->full_name }}" class="profile-avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
            </div>
            <h2>{{ $student->full_name }}</h2>
            <span class="profile-role-badge is-student">{{ucfirst($account->patient_type)}}</span>
        </aside>

        <section class="profile-details-card">
            <div class="profile-details-header">
                <div>
                    <p class="eyebrow">Patient details</p>
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
                <span>For profile updates, please contact the clinic or EMR system administrator.</span>
            </div>
        </section>
    </div>
</div>
@endsection
