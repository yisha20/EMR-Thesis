@extends('layouts.app')

@section('content')
@php
    $visibleStaff = $clinicStaff->take(6);
    $visibleServices = $services->take(5);
    $status = optional($currentComplaint)->status;
    $concernSteps = [
        ['label' => 'Submitted', 'done' => (bool) $currentComplaint, 'current' => $status === 'Pending'],
        ['label' => 'Reviewed', 'done' => $currentComplaint && $status !== 'Pending', 'current' => $status === 'Reviewed'],
        ['label' => 'Consultation', 'done' => $currentComplaint && in_array($status, ['In Consultation', 'Completed'], true), 'current' => $status === 'In Consultation'],
        ['label' => 'Completed', 'done' => $status === 'Completed' || $status === 'Counter Resolved', 'current' => $status === 'Completed' || $status === 'Counter Resolved'],
    ];
    $statusTone = [
        'Completed' => 'is-completed',
        'Counter Resolved' => 'is-completed',
        'In Consultation' => 'is-consultation',
        'Reviewed' => 'is-reviewed',
        'Pending' => 'is-submitted',
    ][$status] ?? 'is-submitted';
    $currentDoctor = $currentComplaint && $currentComplaint->consultation
        ? optional($currentComplaint->consultation->doctor)->fullName()
        : null;
@endphp

<div class="student-dashboard student-portal-page student-dashboard-portal">
    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <div class="student-dashboard-grid">
        <main class="student-main-stack">
            <section class="student-hero-card">
                <div>
                    <p class="eyebrow">Student clinic portal</p>
                    <h1>Welcome, {{ $student->first_name }}</h1>
                    <p>Manage your clinic concerns, prescriptions, and health records.</p>
                    <div class="student-hero-meta">
                        <span><i class="fa fa-id-card-o"></i>{{ $student->student_id_number }}</span>
                        <span><i class="fa fa-university"></i>{{ $student->college_department ?: 'Department not set' }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#studentConcernModal"><i class="fa fa-plus"></i> Submit New Concern</button>
            </section>

            <section class="student-update-cards" aria-label="Clinic updates">
                <article class="student-update-card">
                    <div class="student-update-copy">
                        <span class="student-update-dot"></span>
                        <strong>Clinic Hours</strong>
                        <small>Monday to Friday</small>
                        <b>8:00 AM - 5:00 PM</b>
                    </div>
                    <span class="student-update-icon"><i class="fa fa-clock-o"></i></span>
                </article>
                <article class="student-update-card">
                    <div class="student-update-copy">
                        <span class="student-update-dot is-warning"></span>
                        <strong>Emergency Reminder</strong>
                        <small>Severe symptoms?</small>
                        <b>Contact emergency services</b>
                    </div>
                    <span class="student-update-icon is-warning"><i class="fa fa-exclamation-circle"></i></span>
                </article>
                <article class="student-update-card">
                    <div class="student-update-copy">
                        <span class="student-update-dot"></span>
                        <strong>Announcements</strong>
                        <small>Before clinic visit</small>
                        <b>Bring your IIT ID</b>
                    </div>
                    <span class="student-update-icon"><i class="fa fa-bullhorn"></i></span>
                </article>
            </section>

            <section class="dashboard-panel student-current-complaint student-focus-card">
                @if ($currentComplaint)
                    <div class="student-concern-modern">
                        <div class="student-concern-modern-header">
                            <h2><i class="fa fa-heartbeat"></i> Current Clinic Concern</h2>
                            <span class="student-status-pill {{ $statusTone }}">{{ $currentComplaint->status }}</span>
                        </div>

                        <div class="student-concern-primary">
                            <h3>{{ $currentComplaint->chief_complaint }}</h3>
                            <p>
                                {{ $currentComplaint->complaint_category ?: 'General Consultation' }}
                                <span>&bull;</span>
                                {{ $currentComplaint->urgency_level }}
                                <span>&bull;</span>
                                {{ $currentComplaint->submitted_at->format('M j, Y') }}
                                <span>&bull;</span>
                                {{ $currentComplaint->submitted_at->format('g:i A') }}
                            </p>
                        </div>

                        <div class="student-symptom-quote">
                            <span>Symptoms</span>
                            <p>{{ $currentComplaint->symptoms_description }}</p>
                        </div>

                        <div class="student-concern-info-grid">
                            <div><i class="fa fa-exclamation-triangle"></i><span>Priority</span><strong>{{ $currentComplaint->urgency_level }}</strong></div>
                            <div><i class="fa fa-calendar-o"></i><span>Submitted</span><strong>{{ $currentComplaint->submitted_at->format('M j, Y') }}</strong></div>
                            <div><i class="fa fa-stethoscope"></i><span>Category</span><strong>{{ $currentComplaint->complaint_category ?: 'General Consultation' }}</strong></div>
                            <div><i class="fa fa-user-md"></i><span>Physician</span><strong>{{ $currentDoctor ?: 'Not assigned' }}</strong></div>
                        </div>

                        <div class="student-workflow-line" aria-label="Concern progress">
                            @foreach ($concernSteps as $step)
                                <span class="{{ $step['done'] ? 'is-done' : '' }} {{ $step['current'] ? 'is-current' : '' }}">
                                    <i class="fa {{ $step['done'] ? 'fa-check' : 'fa-circle-o' }}"></i>
                                    <b>{{ $step['label'] }}</b>
                                </span>
                            @endforeach
                        </div>

                        <div class="student-quick-actions">
                            <a href="{{ route('student.complaints.show', $currentComplaint) }}" class="student-action-card"><i class="fa fa-file-text-o"></i><strong>View Details</strong><span>See consultation information</span></a>
                            <a href="{{ route('student.medical-history') }}" class="student-action-card"><i class="fa fa-heartbeat"></i><strong>Health History</strong><span>Past clinic visits</span></a>
                            <a href="{{ route('student.prescriptions.index') }}" class="student-action-card"><i class="fa fa-medkit"></i><strong>My Prescriptions</strong><span>View prescriptions</span></a>
                        </div>
                    </div>
                @else
                    @include('includes.empty-state', [
                        'title' => 'No current concern submitted.',
                        'message' => 'Submit a concern when you need assistance from the clinic.',
                        'icon' => 'fa-file-text-o'
                    ])
                @endif
            </section>

        </main>

        <aside class="student-side-stack">
            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div><p class="eyebrow">Clinic team</p><h2>Available Clinic Staff</h2></div>
                </div>
                <div class="student-staff-list compact-student-list">
                    @forelse ($visibleStaff as $staff)
                        <article>
                            <span class="student-staff-avatar">
                                <img src="{{ $staff->avatar ?? asset('img/no_avatar.jpg') }}" alt="" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';">
                            </span>
                            <div>
                                <strong>{{ trim($staff->fullName()) }}</strong>
                                <span>{{ $staff->role->name }}</span>
                            </div>
                            <span class="staff-availability is-available">Available</span>
                        </article>
                    @empty
                        @include('includes.empty-state', ['title' => 'No clinic staff available at the moment.', 'icon' => 'fa-user-md'])
                    @endforelse
                </div>
            </section>

            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div><p class="eyebrow">Clinic support</p><h2>Available Services</h2></div>
                </div>
                <div class="student-service-list compact-student-list">
                    @forelse ($visibleServices as $service)
                        <article>
                            <div>
                                <strong>{{ $service->name }}</strong>
                                <span class="service-category-badge">{{ $service->category }}</span>
                            </div>
                            <p>{{ \Illuminate\Support\Str::limit($service->description ?: 'Service details are available from clinic staff.', 86) }}</p>
                        </article>
                    @empty
                        @include('includes.empty-state', ['title' => 'No clinic services available.', 'icon' => 'fa-medkit'])
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    @include('student.complaints.partials.intake-modal')
</div>
@endsection
