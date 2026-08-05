@extends('layouts.app')
@section('content')
<div class="container"><h1>Dependent Sponsor Review</h1><p class="text-muted">Records remain preserved until an authorized reviewer selects an action.</p>
@foreach($dependents as $dependent)<div class="card mb-3"><div class="card-body"><h5>{{ $dependent->full_name }}</h5>
<p>Sponsor: {{ optional(optional($dependent->sponsor)->user)->name ?: 'Missing' }} · Type: {{ optional($dependent->sponsor)->patient_type ?: 'Missing' }} · Relationship: {{ $dependent->relationship }} · Status: {{ $dependent->verification_status }} · Created: {{ $dependent->created_at }}</p>
<form method="POST" action="{{ route('patient-dependents.resolve',$dependent) }}">@csrf @method('PATCH')<select name="action" class="form-control mb-2" required><option value="unchanged">Leave pending verification</option><option value="transfer">Transfer to Faculty sponsor</option><option value="independent">Convert to independent record</option><option value="inactive">Mark inactive</option><option value="rejected">Reject relationship</option></select><input name="faculty_sponsor_id" class="form-control mb-2" placeholder="Verified Faculty patient account ID (for transfer)"><textarea name="notes" class="form-control mb-2" placeholder="Review notes"></textarea><button class="btn btn-primary">Record action</button></form>
</div></div>@endforeach {{ $dependents->links() }}</div>
@endsection
