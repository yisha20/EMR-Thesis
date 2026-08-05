<div class="modal fade student-concern-modal complaint-modal" id="studentConcernModal" tabindex="-1" role="dialog" aria-labelledby="studentConcernModalTitle" aria-hidden="true">
    <div class="modal-dialog complaint-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header complaint-modal-header">
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
                <div class="modal-body complaint-modal-body">
                    <div class="student-intake-grid">
                        @if(optional(auth()->user()->patientAccount)->patient_type === 'faculty' && isset($dependents) && $dependents->isNotEmpty())
                        <div class="form-group full-width"><label for="dependent_id">Submitting for</label><select id="dependent_id" name="dependent_id" class="form-control"><option value="">Myself</option>@foreach($dependents as $dependent)<option value="{{ $dependent->id }}">{{ $dependent->full_name }} ({{ $dependent->relationship }})</option>@endforeach</select></div>
                        @endif
                        <fieldset class="form-group full-width"><legend>Dental details (complete only for Dental Services)</legend><textarea name="dental_details" class="form-control" placeholder="Additional dental details"></textarea><input name="pain_duration" class="form-control mt-2" placeholder="Pain duration"><div class="mt-2">@foreach(['swelling'=>'Swelling present','bleeding'=>'Bleeding present','difficulty_eating'=>'Difficulty eating','facial_swelling'=>'Facial swelling','severe_bleeding'=>'Severe bleeding','difficulty_breathing'=>'Difficulty breathing','difficulty_swallowing'=>'Difficulty swallowing','trauma'=>'Trauma','fever'=>'Fever with dental infection','uncontrolled_pain'=>'Uncontrolled pain'] as $v=>$l)<label class="mr-3"><input type="checkbox" name="dental_flags[]" value="{{$v}}"> {{$l}}</label>@endforeach</div><small>Nursing staff determine clinical priority.</small></fieldset>
                        <div class="form-group full-width">
                            <fieldset><legend>Common illnesses and concerns</legend>
                            @foreach($complaintOptions as $category=>$options)
                                @php($visibleOptions=$options->where('name','!=','Other'))
                                @if($visibleOptions->isNotEmpty())
                                <h6 class="concern-category-title">{{ $category }}</h6><div class="complaint-chip-grid concern-options-grid">
                                @foreach($visibleOptions as $option)<label class="complaint-chip"><input type="checkbox" name="complaint_options[]" value="{{ $option->id }}" data-requires-details="{{ $option->requires_details ? 'true':'false' }}" {{ in_array($option->id,old('complaint_options',[]))?'checked':'' }}><span>{{ $option->name }}</span></label>@endforeach
                                </div>
                                @endif
                            @endforeach
                            @php($otherOption=$complaintOptions->flatten()->firstWhere('name','Other'))
                            @if($otherOption)
                            <div class="other-concern-section">
                                <label class="complaint-chip other-concern-toggle"><input type="checkbox" name="complaint_options[]" value="{{ $otherOption->id }}" data-requires-details="true" {{ in_array($otherOption->id,old('complaint_options',[]))?'checked':'' }}><span>Others:</span></label>
                                <div data-other-complaint hidden>
                                    <label class="sr-only" for="other_complaint">Other complaint details</label>
                                    <input id="other_complaint" name="other_complaint" value="{{ old('other_complaint') }}" class="form-control" placeholder="Please specify your other concern">
                                    @error('other_complaint')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @endif
                            </fieldset>
                        </div>
                        <div class="form-group full-width">
                            <label for="symptoms_description">Additional symptom details (optional)</label>
                            <textarea id="symptoms_description" name="symptoms_description" rows="4" class="form-control" placeholder="When symptoms started or anything that makes them better or worse">{{ old('symptoms_description') }}</textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="attachment">Optional Attachment</label>
                            <input id="attachment" type="file" name="attachment" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <small class="form-text text-muted">Accepted files: JPG, PNG, PDF, DOC, or DOCX up to 5 MB.</small>
                        </div>
                    </div>
                    <div class="consultation-info-note"><i class="fa fa-lock"></i><span>Your submission is visible only to authorized clinic staff.</span></div>
                </div>
                <div class="modal-footer complaint-modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane-o"></i> Submit Concern</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script>
(function () {
    var checks = document.querySelectorAll('[name="complaint_options[]"]'), other = document.querySelector('[data-other-complaint]'), input = document.getElementById('other_complaint');
    function syncOther(){var required=[].some.call(checks,function(c){return c.checked&&c.dataset.requiresDetails==='true'});if(!other||!input)return;other.hidden=!required;input.required=required;}
    checks.forEach(function(c){c.addEventListener('change',syncOther)});syncOther();
    @if ($errors->any() && (old('complaint_options') || old('other_complaint') || old('symptoms_description')))
        $(function () { $('#studentConcernModal').modal('show'); });
    @endif
})();
</script>
@endpush
