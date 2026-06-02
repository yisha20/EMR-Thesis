@extends('layouts.app')

@section('content')
<div class="card text-center border-info">
    <div class="card-header border">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="/services">Clinic Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.create') }}">Add New Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
    </div>
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('services.index') }}">View Service... 
            <button type="button" class="close">&times; </button> </a>
          </li>
      </ul>
    </div>
    <div class="card-body">
        <fieldset disabled>
          <div class="form-group row">
            <label for="servicename" class="col-sm-2 col-form-label"><b>Name of Service:</b></label>
            <div class="col-sm-10">
                <textarea class="form-control" id="servicename" rows="1" name="servicename" placeholder="{{ $service->name }}"></textarea>
            </div>
          </div>
          <div class="form-group row">
            <label for="definition" class="col-sm-2 col-form-label"><b>Description:</b></label>
            <div class="col-sm-10">
              <textarea class="form-control" id="definition" rows="3" name="definition" placeholder="{{ $service->description }}"></textarea>
            </div>
          </div>
          <div class="form-group row">
            <label for="addedby" class="col-sm-2 col-form-label"><b>Added by:</b></label>
            <div class="col-sm-10">
              <input type="text" class="form-control" id="addedby" rows="2" name="addedby" placeholder="{{ $service->addedBy->fullName() }}"></textarea>
            </div>
          </div>
       
           
        </fieldset>
    </div>
  </div>
@stop


