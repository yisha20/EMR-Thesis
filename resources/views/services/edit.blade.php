@extends('layouts.app')

@section('content')
  @if ($message = Session::get('success'))
  <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>	
      <strong>{{ $message }}</strong>
  </div>
  @endif
{{---
  @if ($errors->any())
      <div class="alert alert-danger">
          <ul>
              @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
              @endforeach
          </ul>
      </div>
  @endif
  ---}}

<div class="card text-center border-info">
    <div class="card-header border">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('services.index') }}">Clinic Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link " href="{{ route('services.create') }}">Add New Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
    </div>
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('services.index') }}">Edit Service... 
            <button type="button" class="close">&times; </button> </a>
          </li>
      </ul>
    </div>
    <div class="card-body">
      <form class="needs-validation" method="POST" action="{{ route('services.update', $service->id) }}" novalidate>
        @method('PUT')
        @csrf
        <div class="form-group row">
          <label for="servicename" class="col-sm-2 col-form-label"><b>Name of Service:</b></label>
          <div class="col-sm-10">
            <input name="name" value="{{ $service->name }}" type="text" class="form-control" id="validationCustom01" placeholder="service name" required>
          </div>
        </div>

        <div class="form-group row">
          <label for="definition" class="col-sm-2 col-form-label"><b>Description:</b></label>
          <div class="col-sm-10">
            <textarea class="form-control" id="definition" rows="3" name="description">{{ $service->description }}</textarea>
          </div>
        </div>

        <div class="form-group row">
          <label for="added_by" class="col-sm-2 col-form-label"><b>Added by:</b></label>
          <div class="col-sm-10">
            <input  value="{{ $service->addedBy->fullName() }}" disabled type="text" class="form-control">
            <input  value="{{ $service->addedBy->id }}" hidden type="text" class="form-control">
          </div>
        </div>
        <div class="form-group row">
          <label for="added_by" class="col-sm-2 col-form-label"><b>To be Update by:</b></label>
          <div class="col-sm-10">
            <input name="added_by" value="{{ auth()->user()->fullName() }}" disabled type="text" class="form-control">
            <input name="added_by" value="{{ auth()->user()->id }}" hidden type="text" class="form-control">
          </div>
        </div>

            <button class="btn btn-primary" type="submit">Submit form</button>
          </form>
    </div>
  </div>
@endsection


