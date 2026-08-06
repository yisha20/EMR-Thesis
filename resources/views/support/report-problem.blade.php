@extends('layouts.app')
@section('content')
<div style="max-width:760px;margin:0 auto"><header class="mb-4"><p class="text-uppercase text-info font-weight-bold small mb-1">Pilot support</p><h1 style="font-size:28px;font-weight:800">Report a Problem</h1><p class="text-muted">Tell the technical team what failed. Do not include patient names, symptoms, SOAP notes, diagnoses, prescriptions, passwords, or reset tokens.</p></header>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card" style="border:1px solid #bedbec;border-radius:13px"><div class="card-body p-4"><form method="POST" action="{{ route('support.problem.store') }}" enctype="multipart/form-data">@csrf
<div class="form-group"><label for="attempted_action">What were you trying to do?</label><input class="form-control" id="attempted_action" name="attempted_action" maxlength="200" required value="{{ old('attempted_action') }}"></div>
<div class="form-group"><label for="what_happened">What happened?</label><textarea class="form-control" id="what_happened" name="what_happened" maxlength="1000" rows="4" required>{{ old('what_happened') }}</textarea></div>
<div class="form-group"><label for="resource_reference">Queue or complaint reference <span class="text-muted">(optional)</span></label><input class="form-control" id="resource_reference" name="resource_reference" maxlength="80" placeholder="Example: Queue D-004 or Complaint #42" value="{{ old('resource_reference') }}"></div>
<div class="form-group"><label for="screenshot">Screenshot <span class="text-muted">(optional, JPG/PNG, maximum 2 MB)</span></label><input class="form-control-file" type="file" id="screenshot" name="screenshot" accept="image/jpeg,image/png"><small class="text-muted">Make sure the image does not show private patient or clinical information.</small></div>
<div class="form-group"><label for="additional_notes">Additional notes <span class="text-muted">(optional)</span></label><textarea class="form-control" id="additional_notes" name="additional_notes" maxlength="1000" rows="3">{{ old('additional_notes') }}</textarea></div>
<input type="hidden" name="reported_route" value="{{ url()->previous() }}"><input type="hidden" id="device_summary" name="device_summary"><button class="btn btn-primary" type="submit"><i class="fa fa-paper-plane"></i> Submit Problem Report</button>
</form></div></div></div>
<script>document.getElementById('device_summary').value=[screen.width+'x'+screen.height,navigator.platform||'unknown'].join(' / ').slice(0,200);</script>
@endsection
