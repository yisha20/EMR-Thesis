<section class="dashboard-panel complaint-concern-card">
    <div class="dashboard-panel-header">
        <div><p class="eyebrow">Patient concern</p><h2>Complaint and Patient Information</h2></div>
    </div>
    <div class="complaint-concern-body">
        <div class="concern-primary">
            <span>Chief Complaint</span>
            <strong>{{ $complaint->chief_complaint }}</strong>
        </div>
        <div class="concern-description">
            <span>Additional symptom details</span>
            <p>{{ $complaint->symptoms_description ?: 'No additional details provided.' }}</p>
        </div>
        <dl class="complaint-fact-list">
            <div><dt>Category</dt><dd>{{ $complaint->complaint_category ?: 'Not specified' }}</dd></div>
            <div><dt>Selected concerns</dt><dd>{{ $complaint->complaintOptions->pluck('name')->implode(', ') ?: $complaint->chief_complaint }}</dd></div>
            @if($complaint->other_complaint)<div><dt>Other complaint</dt><dd>{{$complaint->other_complaint}}</dd></div>@endif
            <div><dt>Staff triage</dt><dd><span class="urgency-badge urgency-{{ strtolower($complaint->triage_priority) }}">{{ ucfirst($complaint->triage_priority) }}</span></dd></div>
            <div><dt>Patient type</dt><dd>{{ucfirst(optional($complaint->patientAccount)->patient_type ?: 'student')}}</dd></div>
            @if($complaint->dependent)<div><dt>Sponsor / verification</dt><dd>{{optional(optional($complaint->dependent)->sponsor)->identifier}} · {{str_replace('_',' ',ucfirst($complaint->dependent->verification_status))}}</dd></div>@endif
            <div><dt>Department</dt><dd>{{ $complaint->student->college_department }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $complaint->submitted_at->format('M j, Y g:i A') }}</dd></div>
            @if ($complaint->attachment)
                <div><dt>Attachment</dt><dd><a href="{{ route('student.complaints.attachment',$complaint) }}" target="_blank" rel="noopener">Open attachment</a></dd></div>
            @endif
        </dl>
    </div>
</section>
