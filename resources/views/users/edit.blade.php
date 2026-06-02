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
              <a class="nav-link active" href="{{ route('users.index') }}">Edit User... 
              <button type="button" class="close">&times; </button> </a>
            </li>
        </ul>
      </div>
    <div class="card-body">

        <div class="container px-10">
            @if ($message = Session::get('success'))
                <div class="alert alert-success alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>	
                    <strong>{{ $message }}</strong>
                </div>
            @endif
        
            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-group text-center">
                    <div class="col" style=" margin-top: 3%">
                        <img src="{{ $user->avatar ?? '/img/no_avatar.jpg' }}" alt="create_avatar" class="create_avatar"> {{--Update Profile Uploaded (Restrict user thaht only img/png file can be uploaded )--}}
                    </div>
                </div>
                <div class="form-group text-center">
                    <div class="col"><br>
                        <input name="avatar" type="file" style="width: 30%" class="form-control-file border ml-auto mr-auto" accept="image/*">
                    </div>
                    <br>
                </div>

                <div class="container">

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="first_name">First Name:</label>
                                <input id="first_name" name="first_name"type="text" 
                                class="form-control @error('first_name') is-invalid @enderror" 
                                placeholder="First Name *" 
                                value="{{ $user->first_name ? $user->first_name : old('first_name') }}" />
        
                                @error('first_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="middle_name">Middle Name:</label>
                                <input id="middle_name"  name="middle_name" type="text" 
                                class="form-control @error('middle_name') is-invalid @enderror" 
                                placeholder="" 
                                value="{{ $user->middle_name ? $user->middle_name : old('middle_name') }}" />
        
                                @error('middle_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name:</label>
                                <input id="last_name" name="last_name" type="text" 
                                class="form-control @error('last_name') is-invalid @enderror" 
                                placeholder="" 
                                value="{{ $user->last_name ? $user->last_name : old('last_name') }}" />
        
                                @error('last_name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="home_address">Home Address:</label>
                                <input id="home_address" name="home_address" type="text" 
                                class="form-control @error('home_address') is-invalid @enderror" 
                                placeholder="Home Address *" 
                                value="{{ $user->home_address ? $user->home_address : old('home_address') }}" />
        
                                @error('present_address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="form-group">
                                <label for="present_address">Present Address:</label>
                                <input id="present_address"
                                name="present_address"
                                type="text" 
                                class="form-control @error('present_address') is-invalid @enderror" 
                                placeholder="Present Address *" 
                                value="{{ $user->present_address ? $user->present_address : old('present_address') }}" />
        
                                @error('present_address')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail Address:</label>
                                <input id="email" name="email" type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                placeholder="Email Address *" 
                                value="{{ $user->email ? $user->email : old('email') }}" />
        
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input id="password" name="password" type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="New Password *" 
                                value="{{ old('password') }}" />
        
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
                                placeholder="Confirm New Password *" 
                                value="{{ old('password') }}" />
        
                                @error('password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                           
                        </div>


                         <div class="col">
                            <div class="form-group">
                                <label class="mt-3">Gender:</label>
                                <div class="form-check">

                                    <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input"  id="malegender" name="gender" {{ $user->gender == 'male' ? 'checked' : '' }} value="male">
                                        <label class="custom-control-label" for="malegender">Male</label>
                                      </div>   
                                      <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input" id="femalegender" name="gender" {{ $user->gender == 'female' ? 'checked' : '' }} value="female">
                                        <label class="custom-control-label" for="femalegender">Female</label> 
                                        <br>
                                    </div> 
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input id="age"name="age"type="number" 
                                class="form-control @error('age') is-invalid @enderror" 
                                placeholder="Age *" 
                                value="{{ $user->age ? $user->age : old('age') }}" />
        
                                @error('age')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="bdate">Birth Date:</label>
                                <div style="display: none">
                                    {{ $userBirthdate = date('Y-m-d', strtotime($user->birthdate)) }}
                                </div>
                                <input id="bdate" name="birthdate" type="date" 
                                class="form-control @error('birthdate') is-invalid @enderror" 
                                placeholder="Birth Date *" 
                                value="{{ $userBirthdate ? $userBirthdate : old('birthdate') }}" />
                                @error('birthdate')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="civilstat">Civil Status:</label>
                                <select name="civil_status" id="civilstat"
                                value="{{ $user->civil_status ? $user->civil_status : old('civil_status') }}"
                                class="form-control @error('civil_status') is-invalid @enderror">
                                    <option class="hidden" @if(!$user->civil_status) selected @endif disabled>Civil Status</option>
                                    <option value="Single" @if($user->civil_status == 'Single') selected @endif>Single</option>
                                    <option value="Married" @if($user->civil_status == 'Married') selected @endif>Married</option>
                                    <option value="Widowed" @if($user->civil_status == 'Widowed') selected @endif>Widowed</option>
                                    <option value="Separated" @if($user->civil_status == 'Separated') selected @endif>Separated</option>
                                    <option value="In certain cases" @if($user->civil_status == 'In certain cases') selected @endif>In certain cases</option>
                                </select>
        
                                @error('civil_status')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="form-group">
                                <label for="phonenum">Phone Number:</label>
                                <input id="phonenum" name="phone_number" type="number" minlength="10" maxlength="11" 
                                class="form-control @error('phone_number') is-invalid @enderror" 
                                placeholder="Phone Number *" 
                                value="{{ $user->phone_number }}" />
        
                                @error('phone_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        
                            <div class="form-group">
                                <label for="license_number">License Number:</label>
                                <input id="license_number" name="license_number" type="number" 
                                class="form-control @error('license_number') is-invalid @enderror" 
                                placeholder="License Number *" 
                                value="{{ $user->license_number ? $user->license_number : old('license_number') }}" />
        
                                @error('license_number')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="role_id">Role:</label>
                                <select id="role_id"
                                name="role_id"
                                class="form-control @error('role_id') is-invalid @enderror">
                                    <option class="hidden" @if(!$user->role_id) selected @endif disabled>Role</option>
                                    @foreach(\App\Role::get() as $role)
                                        <option value="{{ $role->id }}" @if($user->role_id == $role->id) selected @endif>{{ $role->name }}</option>
                                    @endforeach
                                    {{-- <option value="2" @if($user->role_id == '2') selected @endif>Doctor</option>
                                    <option value="3" @if($user->role_id == '3') selected @endif>Nurse</option> --}}
                                </select>
        
                                @error('role_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="form-group text-right mt-5">
                                <input type="submit" class="btn btn-primary btn-md" value="Save"/>
                                <a href="/users" class="btn btn-secondary btn-md">Cancel</a>
                            </div>
                        </div>
                     </div>
                </div>    
            </form>
        </div>
    </div>
</div>
<style>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    }
</style> 
@stop