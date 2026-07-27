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
                    <p class="eyebrow">Clinic patient portal <span class="badge badge-light">{{ ucfirst($account->patient_type) }}</span></p>
                    <h1>Welcome, {{ $student->first_name }}</h1>
                    <p>Manage your clinic concerns, prescriptions, and health records.</p>
                    <div class="student-hero-meta">
                        <span><i class="fa fa-id-card-o"></i>{{ $student->student_id_number }}</span>
                        <span><i class="fa fa-university"></i>{{ $student->college_department ?: 'Department not set' }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#studentConcernModal"><i class="fa fa-plus"></i> Submit New Concern</button>
            </section>

            <section class="student-update-cards" aria-label="Patient status">
                <article class="student-update-card"><div class="student-update-copy"><strong>Health Assessment</strong><small>{{ str_replace('_',' ',ucfirst($account->health_assessment_status)) }}</small>@if($assessment)<a href="{{ route('health-assessments.pdf',$assessment) }}">Download PDF</a>@endif</div><span class="student-update-icon"><i class="fa fa-file-text-o"></i></span></article>
                <article class="student-update-card" id="patientQueueCard" data-queue-url="{{ route('patient.queue.status') }}"><div class="student-update-copy"><strong>Queue Status</strong>@if($activeQueue)<small>Your Queue Number: <b>{{ $activeQueue->ticket_number }}</b></small><b>{{ ucfirst($activeQueue->status) }}</b>@else<small>No active queue number</small>@endif</div><span class="student-update-icon"><i class="fa fa-users"></i></span></article>
                @if(in_array($account->patient_type,['student','faculty']))<article class="student-update-card"><div class="student-update-copy"><strong>Registered Dependents</strong><small>{{ $dependents->count() }} registered</small><a href="{{ route('patient.dependents.index') }}">Manage dependents</a></div><span class="student-update-icon"><i class="fa fa-user-plus"></i></span></article>@endif
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
                            <h2><i class="fa fa-heartbeat"></i> Latest Clinic Concern</h2>
                            <span class="student-status-pill {{ $statusTone }}">{{ $currentComplaint->status }}</span>
                        </div>

                        <div class="student-concern-primary">
                            <h3>{{ $currentComplaint->chief_complaint }}</h3>
                            <p>
                                {{ $currentComplaint->complaint_category ?: 'General Consultation' }}
                                <span>&bull;</span>
                                Triage: {{ ucfirst($currentComplaint->triage_priority) }}
                                <span>&bull;</span>
                                {{ $currentComplaint->submitted_at->format('M j, Y') }}
                                <span>&bull;</span>
                                {{ $currentComplaint->submitted_at->format('g:i A') }}
                            </p>
                        </div>

                        <div class="student-symptom-quote">
                            <span>Symptoms</span>
                            <p>{{ $currentComplaint->symptoms_description ?: 'No additional details provided.' }}</p>
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

            <section class="dashboard-panel">
                <div class="dashboard-panel-header"><div><p class="eyebrow">Before your visit</p><h2>Important Information</h2></div></div>
                <p class="mb-0">Bring your university ID when visiting the clinic. For severe or life-threatening symptoms, contact emergency services immediately.</p>
            </section>
        </aside>
    </div>

    @include('student.complaints.partials.intake-modal')
</div>
@endsection
@push('js')
<script>(function(){var card=document.getElementById('patientQueueCard');if(!card)return,last=null;function poll(){fetch(card.dataset.queueUrl,{cache:'no-store',credentials:'same-origin',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}).then(function(r){if(!r.ok)throw new Error('Queue refresh failed');return r.json()}).then(function(d){if(!d.queue){card.querySelector('.student-update-copy').innerHTML='<strong>Queue Status</strong><small>No active queue number</small>';last=null;return;}var q=d.queue;card.querySelector('.student-update-copy').innerHTML='<strong>'+q.type.charAt(0).toUpperCase()+q.type.slice(1)+' Queue</strong><small>Your Queue Number: <b>'+q.ticket+'</b> · Now Serving: '+(q.now_serving||'—')+'</small><b>'+q.patients_ahead+' People Ahead · '+q.status+'</b>';if(q.status==='called'&&last!=='called'){var a=document.createElement('div');a.className='queue-turn-banner';a.textContent='It is your turn. Please proceed to the designated clinic area.';document.body.appendChild(a);setTimeout(function(){a.remove()},10000)}last=q.status;}).catch(function(){});}poll();setInterval(poll,20000);document.addEventListener('visibilitychange',function(){if(!document.hidden)poll()});})();</script>
@endpush
