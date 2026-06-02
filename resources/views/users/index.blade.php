@extends('layouts.app')

@section('content')
@if ($message = Session::get('success'))
  <div class="alert alert-success alert-block">
      <button type="button" class="close" data-dismiss="alert">×</button>	
      <strong>{{ $message }}</strong>
  </div>
@endif

<div class="card text-center border-info">
  <div class="card-header border-info">
    <ul class="nav nav-tabs card-header-tabs">
      <li class="nav-item">
        <a class="nav-link active" href="{{ route('users.index') }}">Users</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('users.create') }}">Add New User</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="{{ route('users.archive') }}">Archive</a>
      </li>
    </ul>
  </div>

  <div class="card-body">
    <div class="input-group mb-4" style="margin:auto;max-width:250px">
      <input type="text" id="myInput" placeholder="Search for User" class="form-control">
      <div class="input-group-append">
        <span class="input-group-text"><i class="fa fa-search"></i></span>
      </div>
    </div>

    <table class="table table-responsive-md">
      <thead class="text-center thead-light">
        <tr>
          <th>Role</th>
          <th>Username</th>
          <th>Full Name</th>
          <th>Email Address</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody class="text-center" id="myTable">
        @foreach($users as $user)
          @if(!$user->deleted_at)
            <tr>
              <td>
                <div class="badge 
                  @if($user->role->name == 'Administrator') badge-danger 
                  @elseif($user->role->name == 'Doctor') badge-success
                  @elseif($user->role->name == 'Nurse') badge-primary @endif">
                  {{ $user->role->name }}
                </div>
              </td>
              <td>{{ $user->username }}</td>
              <td>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</td>
              <td>{{ $user->email }}</td>
              <td>
                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm" title="Archive User">
                    <i class="fa fa-archive"></i>
                  </button>
                </form>
              </td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<script>
  $(document).ready(function(){
    $("#myInput").on("keyup", function() {
      var value = $(this).val().toLowerCase();
      $("#myTable tr").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });
  });
</script>
@stop
