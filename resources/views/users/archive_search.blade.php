@extends('layouts.app')

@section('content')


<div class="card text-center border-info">
    <div class="card-header border-info">
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
        <table class="table table-bordered table-responsive-md table-hover">
            <thead class="text-center thead-light">
              
              <tr>
                <th scope="col">Role</th>
                <th scope="col">Last Name</th>
                <th scope="col">First Name</th>
                <th scope="col">Middle Name</th>
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
                  <td>{{ $user->last_name }}</td>
                  <td>{{ $user->first_name }}</td>
                  <td>{{ $user->middle_name }}</td>
                  <td>
                    <form action="{{ route('users.delete', $user->id) }}" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                      @csrf
                      @method('DELETE')
                      <a href="{{ route('users.restore', $user->id) }}"><i class="fa fa-refresh" style="padding-right:20px"aria-hidden="true"></a></i>
                      <button type="submit" class="btn">
                        <i class="fa fa-trash" style="padding-right:15px"aria-hidden="true"></i> 
                      </button>{{--archive nalang daw instead of deleting the files of user--}}
                    </form>
                  </td>
                </tr>
                @endif
              @endforeach
            </tbody>
          </table><br>
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
  const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this user?')) {
      return true
    } else {
      return false
    }
  }
</script>

<script>
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
  });
  </script>
@stop