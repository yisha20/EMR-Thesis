@extends('layouts.app')

@section('content')
<div class="d-block d-sm-none">
<p>This module is available only on Tablet and Desktop View</p>
</div>
<div class="d-none d-sm-block d-sm-none d-md-block">
<div class="container">
    <div class="row justify-content-center">
        <div class="col">
            <div class="card-deck" >
                <div class="card border-info">       
                        <a href="/patients" class="nav-link">
                            <img class="card-img-top" src="img/q1.png" alt="Card image">
                            <p class="card-body text-center">Manage Patients</p>
                        </a>
                </div>
                <div class="card border-info">
                    <a href="/users" class="nav-link">
                        <img class="card-img-top" src="img/q4.png" alt="Card image">
                        <p class="card-body text-center">Manage Users</p>
                    </a>
                </div>
                <div class="card border-info">
                    <a href="{{ route('services.index') }}" class="nav-link">
                        <img class="card-img-top" src="img/q2.png" alt="Card image">
                        <p class="card-body text-center">Manage Services</p>
                    </a>
                </div>
               
             </div>
            </div>
            
        </div>
</div>  
</div>   
@endsection


