<section class="dashboard-panel complaint-concern-card">
    <div class="dashboard-panel-header">
        <div><p class="eyebrow">Student concern</p><h2>Student Concern</h2></div>
    </div>
    <div class="complaint-concern-body">
        <div class="concern-primary">
            <span>Chief Complaint</span>
            <strong>{{ $complaint->chief_complaint }}</strong>
        </div>
        <div class="concern-description">
            <span>Symptoms Description</span>
            <p>{{ $complaint->symptoms_description }}</p>
        </div>
        <dl class="complaint-fact-list">
            <div><dt>Category</dt><dd>{{ $complaint->complaint_category ?: 'Not specified' }}</dd></div>
            <div><dt>Urgency</dt><dd><span class="urgency-badge urgency-{{ strtolower($complaint->urgency_level) }}">{{ $complaint->urgency_level }}</span></dd></div>
            <div><dt>Department</dt><dd>{{ $complaint->student->college_department }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $complaint->submitted_at->format('M j, Y g:i A') }}</dd></div>
            @if ($complaint->attachment)
                <div><dt>Attachment</dt><dd><a href="{{ $complaint->attachment }}" target="_blank" rel="noopener">Open attachment</a></dd></div>
            @endif
        </dl>
    </div>
</section>
