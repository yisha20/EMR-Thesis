@extends('layouts.app')

@section('content')
@php
    $roleName = auth()->user()->role->name;
    $canTriage = in_array($roleName, ['Administrator', 'Nurse', 'Staff'], true);
    $canConsult = in_array($roleName, ['Administrator', 'Doctor'], true);
    $isDoctorReview = $canConsult && $complaint->consultation;
    $isResolved = in_array($complaint->status, ['Counter Resolved', 'Completed'], true);
    $patient = $complaint->patient ?: $matchingPatients->first();
    $previousRecords = $complaint->patient
        ? $complaint->patient->medicalRecords
            ->where('id', '!=', $complaint->medical_record_id)
            ->sortByDesc('date_of_consultation')
            ->take(5)
        : collect();
    $statusLogNames = $complaint->statusLogs->pluck('to_status')->all();
    $hasPatientLink = (bool) $complaint->patient;
    $hasForwarded = $complaint->consultation || in_array($complaint->status, ['Forwarded', 'In Consultation', 'Completed'], true);
    $timelineSteps = [
        ['label' => 'Complaint Submitted', 'done' => true, 'current' => $complaint->status === 'Pending'],
        ['label' => 'Reviewed', 'done' => (bool) $complaint->reviewed_at || in_array('Reviewed', $statusLogNames, true) || $complaint->status !== 'Pending', 'current' => $complaint->status === 'Reviewed'],
        ['label' => 'Patient Linked', 'done' => $hasPatientLink, 'current' => !$hasPatientLink && $complaint->status === 'Reviewed'],
    ];

    if ($complaint->counterService || $complaint->status === 'Counter Resolved') {
        $timelineSteps[] = ['label' => 'Counter Resolved', 'done' => true, 'current' => $complaint->status === 'Counter Resolved'];
    } else {
        $timelineSteps[] = ['label' => 'Forwarded to Doctor', 'done' => $hasForwarded, 'current' => $complaint->status === 'Forwarded'];
        $timelineSteps[] = ['label' => 'In Consultation', 'done' => in_array($complaint->status, ['In Consultation', 'Completed'], true), 'current' => $complaint->status === 'In Consultation'];
        $timelineSteps[] = ['label' => 'Completed', 'done' => $complaint->status === 'Completed', 'current' => $complaint->status === 'Completed'];
    }
    $selectedAction = old('remedy_given') || old('outcome')
        ? 'counter'
        : (old('service_needed') || old('priority') || old('nurse_notes') ? 'consultation' : null);
    $issuedPrescription = $complaint->consultation ? $complaint->consultation->prescription()->with(['patient', 'doctor'])->first() : null;
    $issuedPrescriptionPanelId = $issuedPrescription ? 'complaint-prescription-' . $issuedPrescription->id : null;
    $openPrescriptionId = session('open_prescription_id');
    $printPrescriptionId = session('print_prescription_id');
    $shouldOpenIssuedPrescription = $issuedPrescription && (int) $openPrescriptionId === (int) $issuedPrescription->id;
    $shouldPrintIssuedPrescription = $issuedPrescription && (int) $printPrescriptionId === (int) $issuedPrescription->id;
    $healthSummary = optional($complaint->patientAccount)->latestAssessment;
    $medicalCertificate = $complaint->consultation
        ? $complaint->consultation->medicalCertificates()->whereIn('status', ['draft', 'issued'])->latest('id')->first()
        : null;
@endphp

<div class="dashboard-wrap complaint-workflow-page {{ $isDoctorReview ? 'doctor-consultation-review' : '' }}">
    @if ($message = Session::get('success'))<div class="alert alert-success">{{ $message }}</div>@endif

    <section class="complaint-review-header {{ $isDoctorReview ? 'doctor-case-header' : '' }}">
        <div>
            <p class="eyebrow">{{ $isDoctorReview ? 'Doctor consultation' : 'Student digital intake' }}</p>
            <h1>Complaint Review</h1>
            <div class="complaint-review-identity">
                <strong>{{ $complaint->student_name }}</strong>
                <span>{{ $complaint->student_id_number }}</span>
            </div>
            <div class="complaint-review-meta">
                <span>{{ $complaint->complaint_category ?: 'Uncategorized' }}</span>
                <span class="urgency-badge urgency-{{ strtolower($complaint->triage_priority) }}">{{ $isDoctorReview && $complaint->consultation ? $complaint->consultation->priority : ucfirst($complaint->triage_priority) }}</span>
                <span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->status) }}">{{ $complaint->status }}</span>
                <span>Submitted: {{ $complaint->submitted_at->format('M j, Y') }} &bull; {{ $complaint->submitted_at->format('g:i A') }}</span>
            </div>
        </div>

        @foreach($complaint->queues()->whereIn('status',['waiting','called','serving'])->get() as $queue)
        <div class="card card-body mb-3"><div class="d-flex flex-wrap justify-content-between align-items-center"><div><h3>{{ ucfirst($queue->queue_type) }} Queue · {{ $queue->ticket_number }}</h3><span class="badge badge-info">{{ ucfirst($queue->status) }}</span></div><div class="d-flex flex-wrap">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-primary ml-2">Open Queue Dashboard</a>
        </div></div></div>
        @endforeach
        <div class="complaint-header-actions">
            <a href="{{ route('student-complaints.index') }}" class="btn btn-light"><i class="fa fa-arrow-left"></i> Back to Queue</a>
            @if(optional($complaint->consultation)->completed_at && optional($complaint->consultation)->doctor_id === auth()->id())
                <a class="btn btn-primary" href="{{ route('consultations.medical-certificates.create', $complaint->consultation) }}">
                    <i class="fa {{ $medicalCertificate && $medicalCertificate->status === 'issued' ? 'fa-file-text-o' : 'fa-plus-circle' }}"></i>
                    {{ $medicalCertificate && $medicalCertificate->status === 'issued' ? 'View Medical Certificate' : ($medicalCertificate ? 'Continue Medical Certificate' : 'Generate Medical Certificate') }}
                </a>
            @endif
        </div>
    </section>

    @if ($isDoctorReview)
        <div class="doctor-consultation-layout">
            <main class="doctor-consultation-main">
                @include('student.staff.partials.concern-card', ['complaint' => $complaint])

                <section class="dashboard-panel consultation-detail-card folder-panel">
                    <button type="button" class="folder-panel-toggle" data-folder-toggle aria-expanded="false" aria-controls="consultation-details-folder">
                        <span class="folder-panel-icon"><i class="fa fa-folder-o"></i></span>
                        <span class="folder-panel-title"><small>Nurse-forwarded details</small><strong>Consultation Details</strong></span>
                        <span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->consultation->status) }}">{{ $complaint->consultation->status }}</span>
                        <i class="fa fa-chevron-down folder-panel-chevron"></i>
                    </button>
                    <div id="consultation-details-folder" class="folder-panel-content" hidden>
                        <div class="summary-detail-list consultation-readonly-list">
                            <div><span>Service Needed</span><strong>{{ $complaint->consultation->service_needed }}</strong></div>
                            <div><span>Priority</span><strong>{{ $complaint->consultation->priority }}</strong></div>
                            <div><span>Forwarded By</span><strong>{{ optional($complaint->consultation->forwarder)->fullName() ?: 'Clinic staff' }}</strong></div>
                            <div><span>Forwarded At</span><strong>{{ $complaint->consultation->forwarded_at->format('M j, Y g:i A') }}</strong></div>
                            <div class="summary-wide"><span>Nurse Notes</span><p>{{ $complaint->consultation->nurse_notes ?: 'No notes provided.' }}</p></div>
                        </div>
                    </div>
                </section>
                @if (in_array(auth()->user()->role->name, ['Administrator','Nurse'], true) && in_array($complaint->consultation->status, ['Pending Consultation','Called'], true))
                    <section class="dashboard-panel mt-3">
                        <h2>Reassign Waiting Consultation</h2>
                        <form method="POST" action="{{ route('consultations.reassign', $complaint->consultation) }}" class="workflow-form workflow-form-grid">
                            @csrf @method('PATCH')
                            <div class="form-group"><label for="reassign_doctor_id">New Doctor</label><select id="reassign_doctor_id" name="doctor_id" class="form-control" required><option value="">Select an available doctor</option>@foreach($availableDoctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->fullName() }} — {{ $doctor->waiting_consultations_count }} waiting</option>@endforeach</select></div>
                            <div class="form-group"><label for="reassignment_reason">Reason for Reassignment</label><textarea id="reassignment_reason" name="reason" class="form-control" required></textarea></div>
                            <button class="btn btn-primary">Reassign Consultation</button>
                        </form>
                    </section>
                @endif

                @if ($complaint->consultation->status === 'Completed')
                    <section class="dashboard-panel consultation-summary-card folder-panel {{ $shouldOpenIssuedPrescription ? 'is-open' : '' }}">
                        <button type="button" class="folder-panel-toggle" data-folder-toggle aria-expanded="{{ $shouldOpenIssuedPrescription ? 'true' : 'false' }}" aria-controls="consultation-summary-folder">
                            <span class="folder-panel-icon"><i class="fa fa-folder-o"></i></span>
                            <span class="folder-panel-title"><small>Completed consultation</small><strong>Consultation Summary</strong></span>
                            <span class="complaint-status status-completed">Completed</span>
                            <i class="fa fa-chevron-down folder-panel-chevron"></i>
                        </button>
                        <div id="consultation-summary-folder" class="folder-panel-content" {{ $shouldOpenIssuedPrescription ? '' : 'hidden' }}>
                            <div class="summary-detail-list">
                                <div><span>Service Needed</span><strong>{{ $complaint->consultation->service_needed }}</strong></div>
                                <div><span>Priority</span><strong>{{ $complaint->consultation->priority }}</strong></div>
                                <div class="summary-wide"><span>Symptoms</span><p>{{ $complaint->consultation->subjective ?: $complaint->chief_complaint ?: 'No symptoms recorded.' }}</p></div>
                                <div class="summary-wide"><span>Clinical Findings</span><p>{{ $complaint->consultation->objective ?: $complaint->consultation->doctor_notes ?: 'No clinical findings recorded.' }}</p></div>
                                <div class="summary-wide"><span>Diagnosis</span><p>{{ $complaint->consultation->assessment ?: $complaint->consultation->diagnosis ?: $complaint->diagnosis ?: 'No diagnosis recorded.' }}</p></div>
                                <div class="summary-wide"><span>Treatment Plan</span><p>{{ $complaint->consultation->plan ?: $complaint->consultation->treatment ?: $complaint->treatment ?: 'No treatment plan recorded.' }}</p></div>
                                <div class="summary-wide"><span>Prescription Summary</span><p>{{ $issuedPrescription ? $issuedPrescription->summary : ($complaint->consultation->prescription ?: 'No prescription issued.') }}</p></div>
                                <div class="summary-wide"><span>Doctor Notes</span><p>{{ $complaint->consultation->doctor_notes ?: 'No doctor notes recorded.' }}</p></div>
                                <div><span>Completed By</span><strong>{{ optional($complaint->consultation->doctor)->fullName() ?: 'Clinic doctor' }}</strong></div>
                                <div><span>Completed At</span><strong>{{ optional($complaint->consultation->completed_at)->format('M j, Y g:i A') ?: 'Not recorded' }}</strong></div>
                            </div>
                            @if ($issuedPrescription)
                                <div class="prescription-action-bar">
                                    <button type="button" class="btn btn-primary" data-prescription-toggle="{{ $issuedPrescriptionPanelId }}" aria-expanded="{{ $shouldOpenIssuedPrescription ? 'true' : 'false' }}" aria-controls="{{ $issuedPrescriptionPanelId }}"><i class="fa fa-eye"></i> View Prescription</button>
                                    <button type="button" class="btn btn-light" data-prescription-print="{{ $issuedPrescriptionPanelId }}"><i class="fa fa-print"></i> Print Prescription</button>
                                    <a href="{{ route('prescriptions.download', $issuedPrescription) }}" class="btn btn-light"><i class="fa fa-download"></i> Download PDF</a>
                                </div>
                                @include('prescriptions.inline-panel', ['prescription' => $issuedPrescription, 'panelId' => $issuedPrescriptionPanelId, 'isOpen' => $shouldOpenIssuedPrescription, 'autoPrint' => $shouldPrintIssuedPrescription])
                            @endif
                        </div>
                    </section>
                @elseif (in_array($complaint->consultation->status, ['Pending Consultation', 'Called'], true))
                    <section class="dashboard-panel doctor-findings-card">
                        <div class="dashboard-panel-header"><div><p class="eyebrow">Ready for consultation</p><h2>Doctor Findings</h2></div></div>
                        <form method="POST" action="{{ route('doctor.consultations.start', $complaint->consultation) }}" class="workflow-form compact-workflow-form">
                            @csrf
                            <span class="doctor-start-note">Start the consultation before entering clinical findings.</span>
                            <button class="btn btn-primary" data-submit-loading="Starting..."><i class="fa fa-stethoscope"></i> Start Consultation</button>
                        </form>
                    </section>
                @elseif ($complaint->consultation->status === 'In Consultation')
                    <section class="dashboard-panel doctor-findings-card">
                        <div class="dashboard-panel-header"><div><p class="eyebrow">Clinical documentation</p><h2>Doctor Findings</h2></div></div>
                        <form method="POST" action="{{ route('student-complaints.complete-consultation', $complaint) }}" enctype="multipart/form-data" class="doctor-consultation-form" data-draft-key="doctor-consultation-{{ $complaint->id }}">
                            @csrf
                            <div class="doctor-findings-grid soap-form-grid">
                                <section class="form-group soap-card"><h3>Symptoms</h3><label for="subjective">Patient history, symptoms, allergies, medications, and concerns</label><textarea name="subjective" id="subjective" rows="6" class="form-control" required>{{ old('subjective', trim($complaint->chief_complaint."\n".$complaint->symptoms_description."\n".optional($complaint->consultation)->nurse_notes)) }}</textarea></section>
                                <section class="form-group soap-card"><h3>Clinical Findings</h3><label for="objective">Vital signs, examination results, observations, and test findings</label><textarea name="objective" id="objective" rows="6" class="form-control" required>{{ old('objective') }}</textarea></section>
                                <section class="form-group soap-card"><h3>Diagnosis</h3><label for="assessment">Diagnosis, possible causes, and clinical impression</label><textarea name="assessment" id="assessment" rows="6" class="form-control" required>{{ old('assessment') }}</textarea></section>
                                <section class="form-group soap-card"><h3>Treatment Plan</h3><label for="plan">Treatment, recommendations, referrals, and follow-up instructions</label><textarea name="plan" id="plan" rows="6" class="form-control" required>{{ old('plan') }}</textarea></section>
                                <div class="form-group form-group-wide"><label for="doctor_notes">Additional Doctor Notes</label><textarea name="doctor_notes" id="doctor_notes" rows="3" class="form-control">{{ old('doctor_notes') }}</textarea></div>
                                <div class="form-group"><label for="follow_up_date">Follow-up Date</label><input type="date" name="follow_up_date" id="follow_up_date" class="form-control" value="{{ old('follow_up_date') }}"></div>
                                <div class="form-group"><label for="consultation_attachment">Attachment</label><input type="file" name="attachment" id="consultation_attachment" class="form-control-file"></div>
                            </div>

                            <section class="prescription-module" data-prescription-section>
                                <div class="prescription-module-header"><div><p class="eyebrow">Optional document</p><h3>Prescription</h3></div><span>Generate a printable PDF when medication, labs, or a certificate is needed.</span></div>
                                <div class="form-group"><label for="prescription_type">Prescription Type</label><select name="prescription_type" id="prescription_type" class="form-control" data-prescription-type><option value="">No prescription required</option>@foreach (['Medication', 'Laboratory Request', 'Medical Certificate', 'Other'] as $type)<option value="{{ $type }}" {{ old('prescription_type') === $type ? 'selected' : '' }}>{{ $type }}</option>@endforeach</select></div>
                                <div class="prescription-medications" data-medication-list data-medication-fields>
                                    <div class="prescription-medications-header"><strong>Medication Rows</strong><button type="button" class="btn btn-light btn-sm" data-add-medication><i class="fa fa-plus"></i> Add Medication</button></div>
                                    @foreach (old('medications', [['medication' => '', 'dosage' => '', 'frequency' => '', 'duration' => '', 'instruction' => '']]) as $index => $medication)
                                        <div class="medication-row" data-medication-row>
                                            <input name="medications[{{ $index }}][medication]" class="form-control" placeholder="Medication name" value="{{ $medication['medication'] ?? '' }}">
                                            <input name="medications[{{ $index }}][dosage]" class="form-control" placeholder="Dosage" value="{{ $medication['dosage'] ?? '' }}">
                                            <input name="medications[{{ $index }}][frequency]" class="form-control" placeholder="Frequency" value="{{ $medication['frequency'] ?? '' }}">
                                            <input name="medications[{{ $index }}][duration]" class="form-control" placeholder="Duration" value="{{ $medication['duration'] ?? '' }}">
                                            <input name="medications[{{ $index }}][instruction]" class="form-control" placeholder="Instructions" value="{{ $medication['instruction'] ?? '' }}">
                                            <button type="button" class="btn table-action-button table-action-danger" data-remove-medication aria-label="Remove medication"><i class="fa fa-trash"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                                @error('medications')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                            </section>

                            <div class="doctor-action-bar">
                                <button type="button" class="btn btn-light" data-save-draft><i class="fa fa-save"></i> Save Draft</button>
                                <button class="btn btn-primary" name="print_after" value="0"><i class="fa fa-check-circle"></i> Complete Consultation</button>
                                <button class="btn btn-primary" name="print_after" value="1"><i class="fa fa-print"></i> Complete &amp; Print Prescription</button>
                                <span class="draft-save-status" data-draft-status role="status"></span>
                            </div>
                        </form>
                    </section>
                @endif
            </main>

            <aside class="doctor-consultation-sidebar">
                @include('student.staff.partials.history-card', ['complaint' => $complaint, 'matchingPatients' => $matchingPatients, 'patient' => $patient, 'previousRecords' => $previousRecords])
                @include('student.staff.partials.status-timeline', ['timelineSteps' => $timelineSteps, 'complaint' => $complaint])
            </aside>
        </div>
    @else
        <div class="complaint-review-grid">
            @include('student.staff.partials.concern-card', ['complaint' => $complaint])
            <section class="dashboard-panel assessment-summary-card">
                <div class="dashboard-panel-header">
                    <div><p class="eyebrow">Clinical context</p><h2>Health Assessment Summary</h2></div>
                    @if($healthSummary)<div class="assessment-summary-actions"><a class="btn btn-sm btn-light" href="{{route('patient.assessment.staff',$healthSummary)}}">Open Full Health Assessment</a><a class="btn btn-sm btn-outline-primary" href="{{route('health-assessments.pdf',$healthSummary)}}">Download PDF</a></div>@endif
                </div>
                @if($healthSummary)
                @php($allergyText=$healthSummary->medicalHistories->filter(function($item){return strpos($item->condition,'Allergies') === 0;})->pluck('notes')->filter()->implode(', '))
                @if($allergyText)<div class="clinical-alert"><strong>Known Allergy Alert</strong><span>{{ $allergyText }}</span></div>@endif
                <dl class="assessment-summary-list">
                    <dt>Assessment status</dt><dd>{{str_replace('_',' ',ucfirst($healthSummary->status))}}</dd>
                    <dt>Assessment date</dt><dd>{{optional($healthSummary->submitted_at)->format('d M Y') ?: 'Not provided'}}</dd>
                    <dt>Known allergies</dt><dd>{{$allergyText ?: ($healthSummary->medicalHistories->pluck('condition')->contains('Allergies') ? 'Reported' : 'None reported')}}</dd>
                    <dt>Current medications</dt><dd>{{$healthSummary->medications->pluck('medication')->implode(', ') ?: 'None reported'}}</dd>
                    <dt>Past medical history</dt><dd>{{$healthSummary->medicalHistories->pluck('condition')->implode(', ') ?: 'None reported'}}</dd>
                    <dt>Family history</dt><dd>{{$healthSummary->familyHistories->pluck('condition')->implode(', ') ?: 'None reported'}}</dd>
                    <dt>Social history</dt><dd>Smoking: {{data_get($healthSummary,'social_history.smoking_status','Not reported')}}; Alcohol: {{data_get($healthSummary,'social_history.drinks_alcohol','Not reported')}}</dd>
                    @if(optional($complaint->patientAccount)->patient_type==='dependent')<dt>Registered dependents</dt><dd>{{optional($complaint->patientAccount)->dependents ? $complaint->patientAccount->dependents->count() : 0}}</dd>@endif
                    @php($critical=array_intersect((array)data_get($complaint->intake_details,'dental_flags',[]),['facial_swelling','severe_bleeding','difficulty_breathing','difficulty_swallowing','trauma','fever','uncontrolled_pain']))
                    @if($critical)<div class="alert alert-danger"><strong>Potential urgent dental condition.</strong> Immediate clinical evaluation is recommended. This alert is not a diagnosis.</div>@endif
                </dl>
                @else
                    <p class="text-muted mb-0">No digital health assessment is linked to this patient.</p>
                @endif
            </section>
        </div>

        @if($canTriage && $complaint->status === 'Reviewed')
        <section class="dashboard-panel queue-routing-card">
            <div class="dashboard-panel-header"><div><p class="eyebrow">Digital queue number</p><h2>Queue Routing</h2></div></div>
            <form method="POST" action="{{route('clinic-queues.store',$complaint)}}" class="queue-routing-form" data-queue-routing>
                @csrf
                <div class="form-group"><label for="queue_type">Route to</label><select id="queue_type" name="queue_type" class="form-control"><option value="counter">Counter Service</option><option value="consultation">Doctor Consultation</option></select></div>
                <div class="form-group"><label for="queue_priority">Staff Triage Priority</label><select id="queue_priority" name="priority" class="form-control">@foreach(['low','moderate','high','urgent'] as $priority)<option value="{{$priority}}">{{ucfirst($priority)}}</option>@endforeach</select></div>
                <div class="form-group" data-queue-doctor hidden><label for="assigned_doctor_id">Assigned Doctor</label><select id="assigned_doctor_id" name="assigned_doctor_id" class="form-control" disabled required><option value="">Select an available doctor</option>@foreach($availableDoctors as $doctor)<option value="{{ $doctor->id }}">Dr. {{ $doctor->fullName() }} — {{ optional($doctor->doctorProfile)->specialty ?: 'General Medicine' }} — Available — {{ $doctor->waiting_consultations_count }} waiting</option>@endforeach</select></div>
                <button class="btn btn-primary" data-queue-submit>Add to Counter Queue</button>
            </form>
        </section>
        @endif

        @foreach($complaint->queues()->whereIn('status',['waiting','called','serving'])->get() as $queue)
        <section class="dashboard-panel active-queue-card">
            <div><p class="eyebrow">{{ucfirst($queue->queue_type)}} queue</p><h2>Queue Number {{$queue->ticket_number}}</h2><span class="badge badge-info">{{ucfirst($queue->status)}}</span></div>
            <div class="queue-control-actions">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">Open Queue Dashboard</a>
            </div>
        </section>
        @endforeach

        <section class="complaint-emr-section">
            @include('student.staff.partials.history-card', ['complaint' => $complaint, 'matchingPatients' => $matchingPatients, 'patient' => $patient, 'previousRecords' => $previousRecords])
        </section>

        @if ($canTriage && $complaint->status === 'Pending')
            <section class="dashboard-panel workflow-action-panel">
                <div class="dashboard-panel-header"><div><p class="eyebrow">Nurse review</p><h2>Review Complaint</h2></div></div>
                <form method="POST" action="{{ route('student-complaints.status', $complaint) }}" class="workflow-form compact-workflow-form">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="Reviewed">
                    <div class="form-group"><label for="review_notes">Review notes</label><textarea name="notes" id="review_notes" rows="3" class="form-control" placeholder="Optional initial assessment"></textarea></div>
                    <button class="btn btn-primary"><i class="fa fa-check"></i> Mark as Reviewed</button>
                </form>
            </section>
        @endif

        @if ($canTriage && $complaint->status === 'Reviewed' && !$isResolved)
            <section class="dashboard-panel complaint-decision-panel">
                <div class="dashboard-panel-header"><div><p class="eyebrow">Choose action</p><h2>What action should I take next?</h2></div></div>
                <div class="workflow-decision-grid" data-action-chooser>
                    <article class="workflow-decision-card {{ $selectedAction === 'counter' ? 'is-selected' : '' }}">
                        <div><i class="fa fa-medkit"></i><h3>Resolve at Counter</h3><p>For warm compress, basic medication, wound cleaning, BP monitoring, or health advice.</p></div>
                        <button type="button" class="btn btn-primary" data-workflow-action="counter">Resolve at Counter</button>
                    </article>
                    <article class="workflow-decision-card {{ $selectedAction === 'consultation' ? 'is-selected' : '' }}">
                        <div><i class="fa fa-user-md"></i><h3>Forward to Doctor</h3><p>For checkup, consultation, dental care, physical examination, laboratory request, or clinic service.</p></div>
                        <button type="button" class="btn btn-primary" data-workflow-action="consultation">Forward to Consultation</button>
                    </article>
                </div>
            </section>

            <section class="selected-action-shell" data-selected-action-shell {{ $selectedAction ? '' : 'hidden' }}>
                <article class="dashboard-panel workflow-action-panel" data-workflow-form="counter" {{ $selectedAction === 'counter' ? '' : 'hidden' }}>
                    <div class="dashboard-panel-header"><div><p class="eyebrow">Selected action</p><h2>Resolve at Counter</h2></div></div>
                    <form method="POST" action="{{ route('student-complaints.resolve-counter', $complaint) }}" class="workflow-form workflow-form-grid">
                        @csrf
                        <div class="form-group form-group-wide"><label for="remedy_given">Remedy/action given</label><textarea name="remedy_given" id="remedy_given" rows="3" class="form-control" required placeholder="e.g. Warm compress, wound cleaning, health advice">{{ old('remedy_given') }}</textarea></div>
                        <div class="form-group"><label for="quantity">Quantity, if applicable</label><input name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}"></div>
                        <div class="form-group"><label for="outcome">Outcome</label><select name="outcome" id="outcome" class="form-control" required>@foreach (['Resolved', 'Advised to return if symptoms persist', 'Referred for consultation'] as $outcome)<option value="{{ $outcome }}" {{ old('outcome', 'Resolved') === $outcome ? 'selected' : '' }}>{{ $outcome }}</option>@endforeach</select></div>
                        <div class="form-group form-group-wide"><label for="counter_notes">Notes</label><textarea name="notes" id="counter_notes" rows="3" class="form-control">{{ old('notes') }}</textarea></div>
                        <button class="btn btn-primary"><i class="fa fa-medkit"></i> Save Counter Remedy</button>
                    </form>
                </article>

                <article class="dashboard-panel workflow-action-panel" data-workflow-form="consultation" {{ $selectedAction === 'consultation' ? '' : 'hidden' }}>
                    <div class="dashboard-panel-header"><div><p class="eyebrow">Selected action</p><h2>Forward to Consultation</h2></div></div>
                    <form method="POST" action="{{ route('student-complaints.forward', $complaint) }}" class="workflow-form workflow-form-grid">
                        @csrf
                        <div class="form-group"><label for="service_needed">Service needed</label><select name="service_needed" id="service_needed" class="form-control" required>@foreach (['Checkup', 'Medical Consultation', 'Dental Consultation', 'Physical Examination', 'Laboratory Request', 'Other service'] as $service)<option value="{{ $service }}" {{ old('service_needed') === $service ? 'selected' : '' }}>{{ $service === 'Other service' ? 'Other' : $service }}</option>@endforeach</select></div>
                        <div class="form-group"><label for="priority">Staff Triage Priority</label><select name="priority" id="priority" class="form-control" required>@foreach (['Low', 'Moderate', 'High', 'Urgent'] as $priority)<option value="{{ $priority }}" {{ old('priority', ucfirst($complaint->triage_priority === 'unassigned' ? 'low' : $complaint->triage_priority)) === $priority ? 'selected' : '' }}>{{ $priority }}</option>@endforeach</select></div>
                        <div class="form-group form-group-wide"><label for="doctor_id">Assign Doctor</label><select name="doctor_id" id="doctor_id" class="form-control @error('doctor_id') is-invalid @enderror" required><option value="">Select an available doctor</option>@foreach ($availableDoctors as $doctor)<option value="{{ $doctor->id }}" {{ (int) old('doctor_id') === $doctor->id ? 'selected' : '' }}>Dr. {{ $doctor->fullName() }} — {{ optional($doctor->doctorProfile)->specialty ?: 'General Medicine' }} — Available — {{ $doctor->waiting_consultations_count }} waiting</option>@endforeach</select>@error('doctor_id')<span class="invalid-feedback">{{ $message }}</span>@enderror</div>
                        <div class="form-group form-group-wide"><label for="nurse_notes">Nurse notes</label><textarea name="nurse_notes" id="nurse_notes" rows="4" class="form-control">{{ old('nurse_notes') }}</textarea></div>
                        <button class="btn btn-primary"><i class="fa fa-share"></i> Forward to Doctor</button>
                    </form>
                </article>
            </section>
        @endif

        <div class="complaint-lower-grid">
            <div class="complaint-summary-column">
                @if ($complaint->counterService)
                    <section class="dashboard-panel workflow-summary-panel">
                        <div class="dashboard-panel-header"><div><p class="eyebrow">Counter service completed</p><h2>Remedy Summary</h2></div></div>
                        <div class="summary-detail-list">
                            <div><span>Remedy Given</span><strong>{{ $complaint->counterService->remedy_given }}</strong></div>
                            <div><span>Quantity</span><strong>{{ $complaint->counterService->quantity ?: 'Not applicable' }}</strong></div>
                            <div><span>Outcome</span><strong>{{ $complaint->counterService->outcome }}</strong></div>
                            <div><span>Handled By</span><strong>{{ optional($complaint->counterService->handler)->fullName() ?: 'Clinic staff' }}</strong></div>
                            <div><span>Handled At</span><strong>{{ $complaint->counterService->handled_at->format('M j, Y g:i A') }}</strong></div>
                        </div>
                    </section>
                @endif

                @if ($complaint->consultation)
                    <section class="dashboard-panel consultation-summary-card folder-panel {{ $shouldOpenIssuedPrescription ? 'is-open' : '' }}">
                        <button type="button" class="folder-panel-toggle" data-folder-toggle aria-expanded="{{ $shouldOpenIssuedPrescription ? 'true' : 'false' }}" aria-controls="nurse-consultation-summary-folder">
                            <span class="folder-panel-icon"><i class="fa fa-folder-o"></i></span>
                            <span class="folder-panel-title"><small>Doctor consultation</small><strong>Consultation Summary</strong></span>
                            <span class="complaint-status status-{{ \Illuminate\Support\Str::slug($complaint->consultation->status) }}">{{ $complaint->consultation->status }}</span>
                            <i class="fa fa-chevron-down folder-panel-chevron"></i>
                        </button>
                        <div id="nurse-consultation-summary-folder" class="folder-panel-content" {{ $shouldOpenIssuedPrescription ? '' : 'hidden' }}>
                            <div class="summary-detail-list">
                                <div><span>Service Needed</span><strong>{{ $complaint->consultation->service_needed }}</strong></div>
                                <div><span>Priority</span><strong>{{ $complaint->consultation->priority }}</strong></div>
                                <div><span>Forwarded By</span><strong>{{ optional($complaint->consultation->forwarder)->fullName() ?: 'Clinic staff' }}</strong></div>
                                <div><span>Doctor</span><strong>{{ optional($complaint->consultation->doctor)->fullName() ?: 'Not assigned' }}</strong></div>
                                <div class="summary-wide"><span>Nurse Notes</span><p>{{ $complaint->consultation->nurse_notes ?: 'No notes provided.' }}</p></div>
                                <div class="summary-wide"><span>Diagnosis</span><p>{{ $complaint->consultation->diagnosis ?: $complaint->diagnosis ?: 'No diagnosis recorded.' }}</p></div>
                                <div class="summary-wide"><span>Treatment</span><p>{{ $complaint->consultation->treatment ?: $complaint->treatment ?: 'No treatment recorded.' }}</p></div>
                                <div class="summary-wide"><span>Prescription Summary</span><p>{{ $issuedPrescription ? $issuedPrescription->summary : ($complaint->consultation->prescription ?: 'No prescription issued.') }}</p></div>
                            </div>
                            @if ($issuedPrescription)
                                <div class="prescription-action-bar">
                                    <button type="button" class="btn btn-primary" data-prescription-toggle="{{ $issuedPrescriptionPanelId }}" aria-expanded="{{ $shouldOpenIssuedPrescription ? 'true' : 'false' }}" aria-controls="{{ $issuedPrescriptionPanelId }}"><i class="fa fa-eye"></i> View Prescription</button>
                                    <button type="button" class="btn btn-light" data-prescription-print="{{ $issuedPrescriptionPanelId }}"><i class="fa fa-print"></i> Print Prescription</button>
                                    <a href="{{ route('prescriptions.download', $issuedPrescription) }}" class="btn btn-light"><i class="fa fa-download"></i> Download PDF</a>
                                </div>
                                @include('prescriptions.inline-panel', ['prescription' => $issuedPrescription, 'panelId' => $issuedPrescriptionPanelId, 'isOpen' => $shouldOpenIssuedPrescription, 'autoPrint' => $shouldPrintIssuedPrescription])
                            @endif
                        </div>
                    </section>
                @endif
            </div>
            @include('student.staff.partials.status-timeline', ['timelineSteps' => $timelineSteps, 'complaint' => $complaint])
        </div>
    @endif
</div>
@endsection

@push('js')
<script>
(function () {
    var queueRoute = document.getElementById('queue_type');
    var queueSubmit = document.querySelector('[data-queue-submit]');
    var queueDoctorGroup = document.querySelector('[data-queue-doctor]');
    var queueDoctor = document.getElementById('assigned_doctor_id');
    function syncQueueLabel() {
        if (!queueRoute || !queueSubmit) return;
        queueSubmit.textContent = queueRoute.value === 'counter' ? 'Add to Counter Queue' : 'Forward to Consultation Queue';
        if (queueDoctorGroup && queueDoctor) {
            queueDoctorGroup.hidden = queueRoute.value !== 'consultation';
            queueDoctor.disabled = queueRoute.value !== 'consultation';
        }
    }
    if (queueRoute) {
        queueRoute.addEventListener('change', syncQueueLabel);
        syncQueueLabel();
    }

    var chooser = document.querySelector('[data-action-chooser]');
    var shell = document.querySelector('[data-selected-action-shell]');
    if (chooser && shell) {
        chooser.addEventListener('click', function (event) {
            var button = event.target.closest('[data-workflow-action]');
            if (!button) return;
            var selected = button.dataset.workflowAction;
            shell.hidden = false;
            chooser.querySelectorAll('.workflow-decision-card').forEach(function (card) {
                card.classList.toggle('is-selected', card.contains(button));
            });
            shell.querySelectorAll('[data-workflow-form]').forEach(function (form) {
                form.hidden = form.dataset.workflowForm !== selected;
            });
        });
    }

    document.querySelectorAll('[data-folder-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var content = document.getElementById(toggle.getAttribute('aria-controls'));
            if (!content) return;
            var isOpen = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isOpen));
            content.hidden = isOpen;
            toggle.closest('.folder-panel').classList.toggle('is-open', !isOpen);
        });
    });

    var prescriptionType = document.querySelector('[data-prescription-type]');
    var medicationFields = document.querySelector('[data-medication-fields]');
    function syncPrescriptionFields() {
        if (!prescriptionType || !medicationFields) return;
        medicationFields.hidden = prescriptionType.value === '';
    }
    if (prescriptionType) {
        prescriptionType.addEventListener('change', syncPrescriptionFields);
        syncPrescriptionFields();
    }

    var draftForm = document.querySelector('[data-draft-key]');
    if (draftForm) {
        var draftKey = draftForm.dataset.draftKey;
        var status = draftForm.querySelector('[data-draft-status]');
        try {
            var savedDraft = JSON.parse(localStorage.getItem(draftKey) || '{}');
            draftForm.querySelectorAll('input:not([type=file]), textarea, select').forEach(function (field) {
                if (field.name && savedDraft[field.name] && !field.value) field.value = savedDraft[field.name];
            });
            syncPrescriptionFields();
        } catch (error) {}
        draftForm.querySelector('[data-save-draft]').addEventListener('click', function () {
            var data = {};
            draftForm.querySelectorAll('input:not([type=file]), textarea, select').forEach(function (field) {
                if (field.name) data[field.name] = field.value;
            });
            localStorage.setItem(draftKey, JSON.stringify(data));
            if (status) status.textContent = 'Draft saved in this browser.';
        });
    }

    var list = document.querySelector('[data-medication-list]');
    if (!list) return;
    var nextIndex = list.querySelectorAll('[data-medication-row]').length;

    document.querySelector('[data-add-medication]').addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'medication-row';
        row.setAttribute('data-medication-row', '');
        row.innerHTML = '<input name="medications[' + nextIndex + '][medication]" class="form-control" placeholder="Medication name">' +
            '<input name="medications[' + nextIndex + '][dosage]" class="form-control" placeholder="Dosage">' +
            '<input name="medications[' + nextIndex + '][frequency]" class="form-control" placeholder="Frequency">' +
            '<input name="medications[' + nextIndex + '][duration]" class="form-control" placeholder="Duration">' +
            '<input name="medications[' + nextIndex + '][instruction]" class="form-control" placeholder="Instructions">' +
            '<button type="button" class="btn table-action-button table-action-danger" data-remove-medication aria-label="Remove medication"><i class="fa fa-trash"></i></button>';
        list.appendChild(row);
        nextIndex++;
    });

    list.addEventListener('click', function (event) {
        var button = event.target.closest('[data-remove-medication]');
        if (!button) return;
        var rows = list.querySelectorAll('[data-medication-row]');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach(function (input) { input.value = ''; });
            return;
        }
        button.closest('[data-medication-row]').remove();
    });
})();
</script>
@endpush
