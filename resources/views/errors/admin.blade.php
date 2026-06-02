@extends('layouts.app')

@section('content')
    <div class="alert alert-danger" role="alert">
        <h1 class="alert-heading">Unauthorized</h1>
        <hr>
        <p>You are not authorized to access this instance. You must be an Administrator with the right privileges.</p>
    </div>
@stop