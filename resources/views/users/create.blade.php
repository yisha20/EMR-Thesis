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
                <div class="form-group text-center">
                    <div class="col" style=" margin-top: 3%">
                        <img src="/img/no_avatar.jpg" alt="create_avatar" class="create_avatar"><br> {{--Upload Profile Pic (Restrict user thaht only img/png file can be uploaded--}}
                    </div>
                </div>
                <div class="form-group text-center">
                    <div class="col"><br>
                        <input name="avatar" type="file" style="width: 30%" class="form-control-file border ml-auto mr-auto" accept="image/*">
                    </div>
                </div>
        
                <div class="container"><br>

                    <div class="row">
                        @csrf
                        <div class="col">
                            {{-- <div class="form-group">
                                <input type="number" class="form-control" placeholder="ID Number *" value="" />
                            </div> --}}
                            <div class="form-group">
                                <label for="first_name">First Name:</label>
                                <input id="first_name" name="first_name" type="text" class="form-control" placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name:</label>
                                <input id="middle_name" name="middle_name" type="text" class="form-control" placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name:</label>
                                <input id="last_name" name="last_name" type="text" class="form-control"  placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="home_address">Home Address:</label>
                                <input id="home_address" name="home_address" type="text" class="form-control"  placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="present_address">Present Address:</label>
                                <input id="present_address" name="present_address" type="text" class="form-control"  placeholder="" value="" />
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
                                <label for="password">Password:</label>
                                <input id="password" name="password" type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="" 
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
                                placeholder="" 
                                value="{{ old('password') }}" />
    
                                @error('password')
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
                                        <input type="radio" class="custom-control-input"  id="malegender" name="gender" value="male">
                                        <label class="custom-control-label" for="malegender">Male</label>
                                      </div>   
                                      <div class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" class="custom-control-input mt-4 " id="femalegender" name="gender" value="female">
                                        <label class="custom-control-label" for="femalegender">Female</label>
                                        <br>
                                    </div> 
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="age">Age:</label>
                                <input id="age" name="age" type="number" class="form-control" placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="bdate">Birth Date:</label>
                                <input id="bdate" name="birthdate" type="date" class="form-control" placeholder="" value="" />
                            </div>
                            <div class="form-group">
                                <label for="civilstat">Civil Status:</label>
                                <select name="civil_status" class="form-control">
                                    <option class="hidden"  selected disabled>Civil Status</option>
                                    <option>Single</option>
                                    <option>Married</option>
                                    <option>Widowed</option>
                                    <option>Separated</option>
                                    <option>In certain cases</option>
                                </select>
                            </div>

                            
                            
                            <div class="form-group">
                                <label for="phonenum">Phone Number:</label>
                                <input id="phonenum" type="number" minlength="10" maxlength="10" name="phone_number" class="form-control" placeholder="" value="" />
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
                            <div class="form-group">
                                <label for="role_id">Role:</label>
                                <select id="role_id" name="role_id" class="form-control">
                                    <option class="hidden"  selected disabled>Role</option>
                                    @foreach(\App\Role::get() as $role)
                                        <option value="{{ $role->id }}">
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group text-right mt-5">
                                <button type ="submit" class = "btn btn-info">Register</button>
                                <a href="/users"><button type ="button" class = "btn btn-secondary">Cancel</button></a>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop