@extends('layouts.app')

@section('content')


<div class="card text-center border-info user-table-card">
    <div class="card-header border-info user-table-header">
        <ul class="nav nav-tabs card-header-tabs">
          <li class="nav-item">
            <a class="nav-link" href="/users">Users</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/users/create">Add New User</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="{{ route('users.archive') }}">Archive</a>
          </li>
        </ul>
      </div>

    <div class="card-body">
      <div class="input-group mb-4" style="margin:auto;max-width:300px">
        <form action="{{ route('users.archive_search') }}" method="POST">
          @csrf
          <div class="row">
            <div class="input-group-prepend">
              <input type="search" name="search" placeholder="Search for User " class="form-control">
              <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
              <a href="{{ route('users.archive') }}" class="form-control col-sm-3">Clear</a>
            </div>
          </div>
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
        <div class="user-table-wrap table-responsive-shell">
          <table class="table table-bordered table-hover user-data-table data-table is-wide">
            <thead class="text-center thead-light">
              
              <tr>
                <th scope="col">Role</th>
                <th scope="col">Username</th>
                <th scope="col">Full Name</th>
                <th scope="col">Email Address</th>
                <th scope="col">Action</th>
              </tr>
            </thead>

            <tbody class="p2 text-center" id="myTable">
              @foreach ($users as $user)
                @if($user->trashed())
                <tr>
                  <td>
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
                  </td>
                  <td>{{ $user->username }}</td>
                  <td>{{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}</td>
                  <td>{{ $user->email }}</td>
                  <td class="action-cell">
                    <form action="{{ route('users.delete', $user->id) }}" method="post" class="table-action-group">
                      @csrf
                      @method('DELETE')
                      <a href="{{ route('users.restore', $user->id) }}" class="table-action-button" title="Restore User"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                      <button type="submit" class="table-action-button table-action-danger btn" title="Delete User" data-confirm="Permanently delete {{ $user->fullName() }}? This cannot be undone." data-confirm-title="Delete user">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                      </button>{{--archive nalang daw instead of deleting the files of user--}}
                    </form>
                  </td>
                </tr>
                @endif
              @endforeach
            </tbody>
          </table>
        </div><br>
          <div class="pagination justify-content-center">
            {{$users->links()}}
            </div>
  </div>

  <script>
    $(document).ready(function(){
      $("#myInput").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#myTable tr").filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
      });
    });
    </script>

<script>
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
  });
  </script>
@stop
