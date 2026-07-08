<section class="health-timeline" aria-label="Patient health history">
    @forelse ($records as $record)
        @php
            $type = $record->record_type ?: ($record->counterService ? 'Counter Remedy' : 'Consultation');
            $isCounter = $type === 'Counter Remedy';
            $isPrescription = $type === 'Prescription' || $record->prescription;
            $action = $isPrescription
                ? optional($record->prescription)->summary
                : ($isCounter
                ? optional($record->counterService)->remedy_given
                : ($record->diagnosis ?: $record->recommendation ?: $record->performed_service));
            $handledBy = $isPrescription
                ? optional(optional($record->prescription)->doctor)->fullName()
                : ($isCounter
                ? optional(optional($record->counterService)->handler)->fullName()
                : ($record->attending_physician ?: optional($record->attendingStaff)->fullName()));
            $recordDate = $record->date_of_consultation ?: $record->created_at;
        @endphp
        <article class="health-timeline-item">
            <div class="health-timeline-date">
                <strong>{{ optional($recordDate)->format('M j, Y') ?: 'Date not set' }}</strong>
                <span>{{ $record->time_of_consultation ? \Carbon\Carbon::parse($record->time_of_consultation)->format('g:i A') : '' }}</span>
            </div>
            <span class="health-timeline-marker {{ $isCounter ? 'is-counter' : ($isPrescription ? 'is-prescription' : 'is-consultation') }}"><i class="fa {{ $isCounter ? 'fa-medkit' : ($isPrescription ? 'fa-file-text-o' : 'fa-stethoscope') }}"></i></span>
            <div class="health-timeline-card">
                <div class="health-timeline-card-heading">
                    <div><p class="eyebrow">{{ $record->source ?: ($isCounter ? 'Student Intake / Counter Service' : 'Doctor Consultation') }}</p><h2>{{ $type }}</h2></div>
                    <span class="complaint-status status-{{ \Illuminate\Support\Str::slug($record->outcome ?: $record->consultation_status ?: 'Completed') }}">{{ $record->outcome ?: $record->consultation_status ?: 'Completed' }}</span>
                </div>
                <dl class="health-record-summary">
                    <div><dt>Chief Complaint</dt><dd>{{ $record->chief_complaint ?: 'Not specified' }}</dd></div>
                    <div><dt>{{ $isPrescription ? 'Prescription Summary' : ($isCounter ? 'Action Taken' : 'Diagnosis / Action') }}</dt><dd>{{ $action ?: 'Not specified' }}</dd></div>
                    <div><dt>{{ $isCounter ? 'Handled By' : 'Doctor' }}</dt><dd>{{ $handledBy ?: 'Not assigned' }}</dd></div>
                    @if (!$isCounter && !$isPrescription && $record->medication_taken)<div><dt>Prescription</dt><dd>{{ $record->medication_taken }}</dd></div>@endif
                </dl>
                <div class="health-timeline-actions">
                    @if ($isPrescription && $record->prescription)
                        @php $panelId = 'timeline-prescription-' . $record->prescription->id; @endphp
                        <button type="button" class="btn btn-primary btn-sm" data-prescription-toggle="{{ $panelId }}" aria-expanded="false" aria-controls="{{ $panelId }}"><i class="fa fa-eye"></i> View Prescription</button>
                        @if (empty($studentView))
                            <button type="button" class="btn btn-light btn-sm" data-prescription-print="{{ $panelId }}"><i class="fa fa-print"></i> Print</button>
                            <a href="{{ route('prescriptions.download', $record->prescription) }}" class="btn btn-light btn-sm"><i class="fa fa-download"></i> PDF</a>
                        @endif
                    @elseif (!empty($studentView) && $record->student_complaint_id)
                        <a href="{{ route('student.complaints.show', $record->student_complaint_id) }}" class="btn btn-light btn-sm">View Details</a>
                    @elseif ($record->student_complaint_id)
                        <a href="{{ route('student-complaints.show', $record->student_complaint_id) }}" class="btn btn-light btn-sm">View Workflow Details</a>
                    @elseif (!$isCounter)
                        <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-light btn-sm">View Details</a>
                    @endif
                    @if ($record->file)<a href="{{ $record->file }}" target="_blank" rel="noopener" class="btn btn-light btn-sm"><i class="fa fa-paperclip"></i> Attachment</a>@endif
                </div>
                @if ($isPrescription && $record->prescription)
                    @include('prescriptions.inline-panel', ['prescription' => $record->prescription, 'panelId' => $panelId, 'canExport' => empty($studentView)])
                @endif
            </div>
        </article>
    @empty
        @include('includes.empty-state', ['title' => 'No health history yet.', 'message' => 'Counter remedies and completed clinic workflow actions will appear here automatically.', 'icon' => 'fa-heartbeat'])
    @endforelse
</section>
