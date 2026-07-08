@extends('layouts.app')

@section('content')
  @if ($message = Session::get('success'))
  <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>	
      <strong>{{ $message }}</strong>
  </div>
  @endif


<div class="card border-info service-form-card">
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.index') }}">Clinic Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('services.create') }}">Add New Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
    </div>
    <div class="card-body">
      <form method="POST" action="{{ route('services.store') }}" class="service-create-form">
        @csrf
          <div class="service-form-grid">
            <div class="form-group">
              <label for="serviceName">Name of Service</label>
              <input name="name" type="text" class="form-control @error('name') is-invalid @enderror" id="serviceName" placeholder="Enter service name" value="{{ old('name') }}">
              @error('name')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>
            <div class="form-group">
              <label for="serviceCategory">Category</label>
              <select name="category" id="serviceCategory" class="form-control @error('category') is-invalid @enderror">
                @foreach (['Consultation', 'Immunization', 'Treatment', 'Laboratory', 'First Aid'] as $category)
                  <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label for="serviceStatus">Status</label>
              <select name="status" id="serviceStatus" class="form-control @error('status') is-invalid @enderror">
                <option value="Active" selected>Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
            <div class="form-group service-description-field">
              <label for="serviceDescription">Description</label>
              <textarea name="description" class="form-control @error('description') is-invalid @enderror" id="serviceDescription" rows="7" placeholder="Describe the purpose, procedure, and intended patient care for this service">{{ old('description') }}</textarea>
              @error('description')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
              @enderror
            </div>
          </div>

          <div class="service-form-footer">
            <span class="service-created-by"><i class="fa fa-stethoscope"></i> Created By: {{ auth()->user()->fullName() }}</span>
            <button class="btn btn-primary" type="submit">Create Service</button>
          </div>
        </form>
    </div>
  </div>
@endsection
