@extends('layouts.app')

@section('content')


<div class="card text-center border-info">
    <div class="card-header border-info">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('patients.index') }}">Patients</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('patients.create') }}">Add New Patient</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('patients.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
      
</div>
    <div class="card-body">
      <div class="input-group mb-4" style="margin:auto;max-width:250px">
        <form action="{{ route('patients.search') }}" method="POST">
          @csrf
            <div class="row">
                <div class="input-group-prepend">
                  <input type="search" autocomplete="off" name="search" placeholder="Search for Patient " class="form-control">
                  <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
                </div>
            </div>  
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
      <div class="pagination justify-content-center">
        {{$patients->links()}}
        </div>
        <table class="table table-bordered table-responsive-md table-hover">
            <thead class="text-center thead-light">
              <tr>
                <th>Picture</th>
                <th scope="col">OPD/Id Number</th>
                <th scope="col">Last Name</th>
                <th scope="col">First Name</th>
                <th scope="col">Middle Name</th>
                <th scope="col">Action</th>
              </tr>
            </thead>

            <tbody class="p2 text-center" id="myTable">
	          	@foreach ($patients as $patient)
	          	<tr>
                  <td>
                    <img src="{{ $patient->avatar ?? 'img/no_avatar.jpg' }}" style="height: 50px; width: 50px; border-radius: 50%" />
                  </td>
                	<td>{{ $patient->id_number }}</td>
               		<td>{{ $patient->last_name }}</td>
                	<td>{{ $patient->first_name }}</td>
                	<td>{{ $patient->middle_name }}</td>
                	<td>
                  		
                      <form action="{{ route('patients.destroy', $patient->id) }}" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                        @csrf
                        @method('DELETE')
                        <a href="{{ route('patients.show', $patient->id) }}"><i class="fa fa-eye" data-toggle="tooltip" data-placement="top" title="view" style="padding-right:20px"aria-hidden="true"></a></i>
                        <a href="{{ route('patients.edit', $patient->id) }}"><i class="fa fa-edit" data-toggle="tooltip" data-placement="top" title="edit" style="padding-right:20px" aria-hidden="true"></a></i>
                        <button class="btn" type="submit">
                          <i class="fa fa-archive" data-toggle="tooltip" data-placement="top" title="archive" style="padding-right:15px"aria-hidden="true"></i> 
                        </button>{{--archive nalang daw instead of deleting the files of user--}}
                      </form>
                	</td>
	      	      </tr>
            @endforeach
            </tbody>
          </table><br>
          <div class="pagination justify-content-center">
            {{$patients->links()}}
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
    if (confirm('Are you sure you want to archive this patient?')) {
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