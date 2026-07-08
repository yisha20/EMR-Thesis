@extends('layouts.app')

@section('content')


<div class="card text-center border-info patient-table-card">
    <div class="card-header border-info patient-table-header">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('patients.index') }}">Patients</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('patients.create') }}">Add New Patient</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('patients.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
      
</div>
    <div class="card-body">
      <div class="input-group mb-4" style="margin:auto;max-width:300px">
        <form action="{{ route('patients.archive_search') }}" method="POST">
          @csrf
          <div class="row">
            <div class="input-group-prepend">
              <input type="search" name="search" placeholder="Search for Patient " class="form-control">
              <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
              <a href="{{ route('patients.archive') }}" class="form-control col-sm-3">Clear</a>
            </div>
          </div>
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
        <div class="table-responsive-shell patient-table-wrap">
        <table class="table table-bordered table-hover patient-data-table data-table">
            <thead class="text-center thead-light">
              <tr>
                <th scope="col">OPD/Id Number</th>
                <th scope="col">Last Name</th>
                <th scope="col">First Name</th>
                <th scope="col">Middle Name</th>
                <th scope="col">Action</th>
              </tr>
            </thead>

            <tbody class="p2 text-center" id="myTable">
              @foreach ($patients as $patient)
                @if($patient->trashed())
                <tr>
                  <td>{{ $patient->id_number }}</td>
                  <td>{{ $patient->last_name }}</td>
                  <td>{{ $patient->first_name }}</td>
                  <td>{{ $patient->middle_name }}</td>
                  <td class="action-cell">
                    <form action="{{ route('patients.delete', $patient->id) }}" class="table-action-group" method="post">
                      @csrf
                      @method('DELETE')
                      <a href="{{ route('patients.restore', $patient->id) }}" class="table-action-button" data-toggle="tooltip" data-placement="top" title="Restore"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                      <button type="submit" class="btn table-action-button" data-toggle="tooltip" data-placement="top" title="Delete" data-confirm="Permanently delete {{ $patient->first_name }} {{ $patient->last_name }}? This cannot be undone." data-confirm-title="Delete patient">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                      </button>{{--archive nalang daw instead of deleting the files of patient--}}
                    </form>
                  </td>
                </tr>
                @endif
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
