@extends('layouts.app')

@section('content')


<div class="card text-center border-info patient-table-card">
    <div class="card-header border-info patient-table-header">
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
      <div class="input-group mb-4" style="margin:auto;max-width:300px">
        <form action="{{ route('patients.search') }}" method="POST">
          @csrf
          <div class="row">
            <div class="input-group-prepend">
              <input type="search" name="search" placeholder="Search for Patient " class="form-control">
              <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
              <a href="{{ route('patients.index') }}" class="form-control col-sm-3">Clear</a>
            </div>
          </div>
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
        <div class="table-responsive-shell patient-table-wrap">
        <table class="table table-bordered table-hover patient-data-table data-table">
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
                  <img src="{{ $patient->avatar ?? asset('img/no_avatar.jpg') }}" alt="{{ $patient->first_name }} {{ $patient->last_name }}" class="patient-table-avatar" onerror="this.onerror=null;this.src='{{ asset('img/no_avatar.jpg') }}';" />
                </td>
                	<td>{{ $patient->id_number }}</td>
               		<td>{{ $patient->last_name }}</td>
                	<td>{{ $patient->first_name }}</td>
                	<td>{{ $patient->middle_name }}</td>
                  <td class="action-cell">
                  		
                      <form action="{{ route('patients.destroy', $patient->id) }}" class="table-action-group" method="post">
                        @csrf
                        @method('DELETE')
                        <a href="{{ route('patients.show', $patient->id) }}" class="table-action-button" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye" aria-hidden="true"></i></a>
                        <a href="{{ route('patients.edit', $patient->id) }}" class="table-action-button" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-edit" aria-hidden="true"></i></a>
                        <button class="btn table-action-button" type="submit" data-toggle="tooltip" data-placement="top" title="Archive" data-confirm="Archive {{ $patient->first_name }} {{ $patient->last_name }}?" data-confirm-title="Archive patient">
                          <i class="fa fa-archive" aria-hidden="true"></i>
                        </button>{{--archive nalang daw instead of deleting the files of user--}}
                      </form>
                	</td>
	      	      </tr>
            @endforeach
            </tbody>
          </table>
        </div><br>
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
  $(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
  });
  </script>
@stop
