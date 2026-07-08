@extends('layouts.app')

@section('content')
    

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-info user-form-card">
        <div class="card-header border-info">
          <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
              <a class="nav-link" href="{{ route('users.index') }}">Users</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="{{ route('users.create') }}">Add New User</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('users.archive') }}">Archive</a>
              </li>
          </ul>
        </div>
        
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
            <div class="card-body">
                @if ($message = Session::get('success'))
                    <div class="alert alert-success alert-block">
                        <button type="button" class="close" data-dismiss="alert">×</button>	
                        <strong>{{ $message }}</strong>
                    </div>
                @endif
                @csrf
                <div class="user-form-shell form-layout">
                    <aside class="user-photo-panel">
                        <div class="user-photo-frame">
                            <img src="/img/no_avatar.jpg" alt="Staff avatar preview" id="profileDisplay" class="user-form-avatar">
                        </div>
                        <label for="profileImage" class="user-photo-upload">
                            <i class="fa fa-cog"></i>
                            <span>Upload Staff Photo</span>
                        </label>
                        <input id="profileImage" name="avatar" type="file" class="user-photo-input" accept="image/*" onchange="displayImage(this)">
                    </aside>

                    <section class="user-form-fields">
                        <div class="user-form-grid form-fields-grid">
                            <div class="form-group">
                                <label for="first_name">First Name:</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" placeholder="" value="{{ old('first_name') }}" />
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name:</label>
                                <input id="last_name" name="last_name" type="text" class="form-control"  placeholder="" value="{{ old('last_name') }}" />
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name:</label>
                                <input id="middle_name" name="middle_name" type="text" class="form-control" placeholder="" value="{{ old('middle_name') }}" />
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail Address:</label>
                                <input id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder=""
                                value="{{ old('email') }}" />

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="role_id">Role:</label>
                                <select id="role_id" name="role_id" class="form-control">
                                    <option class="hidden" selected disabled>Role</option>
                                    @foreach(\App\Role::get() as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input id="username" name="username" type="text" class="form-control @error('username') is-invalid @enderror" placeholder="" value="{{ old('username') }}" />

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input id="password" name="password" type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="" 
                                value="" />
    
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation">Password Confirmation:</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" 
                                class="form-control @error('password_confirmation') is-invalid @enderror" 
                                placeholder="" 
                                value="" />
    
                                @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="mt-3">Gender:</label>
                                <div class="form-check user-gender-options">

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input"  id="malegender" name="gender" value="male" {{ old('gender') == 'male' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="malegender">Male</label>
                                      </div>   
                                      <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input mt-4 " id="femalegender" name="gender" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="femalegender">Female</label>
                                        <br>
                                    </div> 
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input id="age" name="age" type="number" class="form-control" placeholder="" value="{{ old('age') }}" />
                            </div>
                            <div class="form-group">
                                <label for="bdate">Birth Date:</label>
                                <input id="bdate" name="birthdate" type="date" class="form-control" placeholder="" value="{{ old('birthdate') }}" />
                            </div>
                            <div class="form-group">
                                <label for="civilstat">Civil Status:</label>
                                <select name="civil_status" class="form-control" id="civilstat">
                                    <option class="hidden" selected disabled>Civil Status</option>
                                    <option {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                    <option {{ old('civil_status') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                    <option {{ old('civil_status') == 'In certain cases' ? 'selected' : '' }}>In certain cases</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="home_address">Home Address:</label>
                                <input id="home_address" name="home_address" type="text" class="form-control"  placeholder="" value="{{ old('home_address') }}" />
                            </div>
                            <div class="form-group">
                                <label for="present_address">Present Address:</label>
                                <input id="present_address" name="present_address" type="text" class="form-control"  placeholder="" value="{{ old('present_address') }}" />
                            </div>
                            <div class="form-group">
                                <label for="phonenum">Phone Number:</label>
                                <input id="phonenum" type="number" minlength="10" maxlength="10" name="phone_number" class="form-control" placeholder="" value="{{ old('phone_number') }}" />
                            </div>
                            
                            <div class="form-group">
                                <label for="license_number">License Number:</label>
                                <input id="license_number"
                                name="license_number" 
                                type="number" 
                                class="form-control @error('license_number') is-invalid @enderror" 
                                placeholder="" 
                                value="{{ old('license_number') }}" />
    
                                @error('license_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group user-form-actions">
                                <button type ="submit" class = "btn btn-info">Register</button>
                                <a href="/users"><button type ="button" class = "btn btn-secondary">Cancel</button></a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </div>
@stop
