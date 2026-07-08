<div class="modal fade student-concern-modal" id="studentConcernModal" tabindex="-1" role="dialog" aria-labelledby="studentConcernModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="eyebrow">New concern</p>
                    <h5 class="modal-title" id="studentConcernModalTitle">Submit Chief Complaint</h5>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('student.complaints.store') }}" enctype="multipart/form-data" class="student-intake-form">
                @csrf
                <div class="modal-body">
                    <div class="student-intake-grid">
                        <div class="form-group">
                            <label for="complaint_category">Complaint Category</label>
                            <select id="complaint_category" name="complaint_category" class="form-control" required>
                                <option value="">Select a category</option>
                                @foreach (['General Consultation', 'Injury / First Aid', 'Dental Concern', 'Respiratory Concern', 'Digestive Concern', 'Women\'s Health', 'Mental Health', 'Medication / Prescription', 'Other'] as $category)
                                    <option value="{{ $category }}" {{ old('complaint_category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="urgency_level">Urgency Level</label>
                            <select id="urgency_level" name="urgency_level" class="form-control" required>
                                @foreach (['Low', 'Moderate', 'High'] as $urgency)
                                    <option value="{{ $urgency }}" {{ old('urgency_level', 'Low') === $urgency ? 'selected' : '' }}>{{ $urgency }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="chief_complaint">Chief Complaint</label>
                            <input id="chief_complaint" name="chief_complaint" value="{{ old('chief_complaint') }}" class="form-control" placeholder="Briefly state your main concern" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="symptoms_description">Symptoms Description</label>
                            <textarea id="symptoms_description" name="symptoms_description" rows="5" class="form-control" placeholder="Describe your symptoms, when they started, and anything that makes them better or worse" required>{{ old('symptoms_description') }}</textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="attachment">Optional Attachment</label>
                            <input id="attachment" type="file" name="attachment" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <small class="form-text text-muted">Accepted files: JPG, PNG, PDF, DOC, or DOCX up to 5 MB.</small>
                        </div>
                    </div>
                    <div class="consultation-info-note"><i class="fa fa-lock"></i><span>Your submission is visible only to authorized clinic staff.</span></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane-o"></i> Submit to Clinic</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
(function () {
    @if ($errors->any() && (old('complaint_category') || old('chief_complaint') || old('symptoms_description')))
        $(function () { $('#studentConcernModal').modal('show'); });
    @endif
})();
</script>
@endpush
