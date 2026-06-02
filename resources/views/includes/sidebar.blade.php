@php
$auth = Auth::user(); 
$activeUsers = \App\User::getActive();
@endphp
<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <div id="main">
            <span class="open-slide d-none d-sm-block"style="font-size:30px;cursor:pointer" onclick="openNav()">&#9776; </span>
            </div>
        <a class="navbar-brand" href="{{ url('/dashboard') }}">
            {{ config('app.name', ' EMR') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse " id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
              <li class="nav-item">
                <a class="nav-link" href="/dashboard">Home <span class="sr-only">(current)</span></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/about">About</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/doctors">Clinic Staff</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/contact">Contact</a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-xl-none d-block d-sm-none" href="#">Active Users</a>
              </li>
              <li class="nav-item">
                <a class="nav-link d-xl-none d-md-none d-lg-none " href="#">Help</a>
              </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                <img src="{{ Auth::user()->avatar ?? '/img/no_avatar.jpg' }}" alt="Avatar" class="avatar">
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->first_name . " " . Auth::user()->last_name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>

    <div id="mySidepanel" class="sidepanel">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <div class="prof">
            <a href="{{ route('profile.show', auth()->user()->id) }}" style="text-decoration: none">
                <img src="{{Auth::user()->avatar ?? '/img/no_avatar.jpg' }}" alt="Avatar" class="user-panel"><br>
                <h4 class="text-wrap" style="color:white; width: 14rem">{{Auth::user()->first_name . " " . Auth::user()->middle_name . " " . Auth::user()->last_name}}</h4>
                <p class="role" style="color:gray">
                    <div class="badge 
                        @if(Auth::user()->role->name == 'Administrator') 
                          badge-danger 
                        @elseif(Auth::user()->role->name == 'Doctor')  
                          badge-success
                        @elseif(Auth::user()->role->name == 'Nurse')
                          badge-primary
                        @endif">
                          {{ Auth::user()->role->name }}
                        </div>
                </p>
            </a>
        </div>
        {{--{{ route('users.show', $user->id) }}--}}
        <div class="row">
            <div class="col text-left">
                <a href="{{ route('dashboard') }}"><i class="fa fa-dashboard" style="padding-right:27px"></i>Dashboard</a>
                <a href="{{ route('profile.show', auth()->user()->id) }}"><i class="fa fa-user" style="padding-right:32px"></i>Profile</a> {{--User Profile Must Be Redirected to Users View (show)--}}
                <a href="/patients"><i class="fa fa-tasks" style="padding-right:27px"></i>Manage Patients</a>
                <a href="/users"><i class="fa fa-tasks"style="padding-right:27px"></i>Manage Users</a>
                <a href="/services"><i class="fa fa-tasks" style="padding-right:27px"></i>Manage Services</a>
                
                <a href="{{ route('logout') }}" onclick="event.preventDefault();
                 document.getElementById('logout-form').submit();">
                 <i class="fa fa-power-off" style="padding-right:29px"></i>{{ __('Logout') }}</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                

                <!--   <a href="{{ route('help') }}"><i class="fa fa-question"></i> Help</a> -->
            </div>
        </div>
    </div>      
    


    <!--end of side bar content-->
    
    <!--Active Users-->
    
    
    <button class="open-button" onclick="openForm()">Online</button>

        <div class="chat-popup" id="myForm">
        <form action="/#" class="form-container">
            <h6 style="text-align: center">Active Users</h6>
            <table class="table  table-responsive-sm">
                <thead class="a">
                    <tr>
                        
                    </tr>
                    <tr>
                    </tr>
                </thead>
                <tbody class="b">
                    @foreach($activeUsers as $user)
                    <tr>
                        <td class="col-sm-4">{{ $user->first_name . " " . $user->last_name }}</td>
                        <td><i class="fa fa-circle" style="color: green" aria-hidden="true"></i></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn cancel" onclick="closeForm()">Close</button>
        </form>
        </div>
    <!--End for Active Users-->
</nav>
    <script>

        /*Sidebar*/
    function openNav() {
      document.getElementById("mySidepanel").style.width = "270px";
    }
    
    function closeNav() {
      document.getElementById("mySidepanel").style.width = "0";
    }

        /*OnlineButton*/
    function openForm() {
        document.getElementById("myForm").style.display = "block";
        }

        function closeForm() {
        document.getElementById("myForm").style.display = "none";
        }
    
    </script>
