@extends('layouts.app')

@section('content')
<div class="dashboard-wrap">
    <div class="dashboard-heading"><p class="eyebrow">Doctor settings</p><h1>Prescription Profile</h1><span>Details are snapshotted when each prescription is issued.</span></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <section class="dashboard-panel">
        <form method="POST" action="{{ route('doctor-profile.update', $doctor) }}" class="workflow-form workflow-form-grid">
            @csrf @method('PATCH')
            @foreach ([
                'specialty'=>'Specialty', 'professional_title'=>'Professional Title',
                'clinic_designation'=>'Clinic Designation', 'prc_number'=>'PRC License Number',
                'ptr_number'=>'PTR Number', 's2_number'=>'S2 License Number',
                'contact_number'=>'Professional Contact Number'
            ] as $field => $label)
                <div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $profile->$field) }}" class="form-control" {{ $field === 'prc_number' ? 'required' : '' }}>@error($field)<span class="invalid-feedback d-block">{{ $message }}</span>@enderror</div>
            @endforeach
            <div class="form-group form-group-wide"><label for="clinic_address">Clinic Address</label><textarea id="clinic_address" name="clinic_address" class="form-control" rows="3">{{ old('clinic_address', $profile->clinic_address) }}</textarea></div>
            <div class="form-group form-group-wide"><label for="prescription_footer">Prescription Footer</label><textarea id="prescription_footer" name="prescription_footer" class="form-control" rows="3">{{ old('prescription_footer', $profile->prescription_footer) }}</textarea></div>
            <button class="btn btn-primary">Save Prescription Profile</button>
        </form>
    </section>
    <section class="dashboard-panel mt-3">
        <h2>Availability</h2>
        <form method="POST" action="{{ route('doctor-profile.availability', $doctor) }}" class="form-inline">
            @csrf @method('PATCH')
            <label class="sr-only" for="availability">Availability</label>
            <select id="availability" name="availability" class="form-control mr-2">@foreach(['available'=>'Available','busy'=>'Busy','temporarily_unavailable'=>'Temporarily Unavailable','off_duty'=>'Off Duty'] as $value=>$label)<option value="{{ $value }}" {{ $profile->availability === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <button class="btn btn-primary">Update Availability</button>
        </form>
    </section>
    <section class="dashboard-panel mt-3"><h2>E-signature readiness</h2><p>Status: <strong>{{ str_replace('_', ' ', ucfirst($profile->signature_status)) }}</strong></p><p>Signature uploads remain private and only verified versioned files can be embedded in generated PDFs. No typed or generated signature is used.</p></section>
</div>
@endsection
