@extends('layouts.app')

@section('content')
  <div class="card border-info">
    <div class="card-header border">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="/users">Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/users/create">Add New User</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('users.archive') }}">Archive</a>
        </li>
      </ul>
    </div>
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('users.index') }}">View User... 
            <button type="button" class="close">&times; </button> </a>
          </li>
      </ul>
    </div>

    <div class="card-body">
      <div class="form-group text-center">
        <div class="col" style=" margin-top: 3%">
          <img src="{{ $user->avatar ?? '/img/no_avatar.jpg' }}" alt="create_avatar" class="create_avatar"> {{--Profile Uploaded--}}
        </div>
      </div>
      <div class="form-group text-center">
        <div class="col">
          <h4>{{ "$user->first_name $user->middle_name $user->last_name" }}</h4>
          <div class="badge 
                        @if($user->role->name == 'Administrator') 
                          badge-danger 
                        @elseif($user->role->name == 'Doctor')  
                          badge-success
                        @elseif($user->role->name == 'Nurse')
                          badge-primary
                        @endif">
                          {{ $user->role->name }}
                        </div>
            <br><br>
        </div>
      </div>
      
      <div class="container" >
            <div class="row"> 
              <div class="col-2">
              </div>          
              <div class="col">
                  <h6 class><i>Home Address:</h6></i>
                  <p>{{ $user->home_address }}</p>
                  <h6><i>Present Address:</h6></i>
                  <p>{{ $user->present_address }}</p>
                  <h6><i>E-mail Address:</i></h6>
                  <p>{{ $user->email }}</p>
                  <h6><i>Civil Status:</i></h6>
                  <p>{{ $user->civil_status }}</p>
                  <h6><i>Gender:</i></h6>
                  <p>{{ $user->gender }}</p>
              </div>
              <div class="col">
                <h6><i>Age:</i></h6>
                <p>{{ $user->age }}</p>
                <h6><i>Birth Year/Month/Date:</i></h6>
                <p>{{ $user->birthdate }}</p>
                <h6><i>Phone Number:</i></h6>
                <p>{{ $user->phone_number }}</p>   
                <h6><i>License Number:</i></h6>
                <p>{{ $user-> license_number }}</p>
              </div>
            </div>
                        
                 
    </div>  
  </div>
@stop