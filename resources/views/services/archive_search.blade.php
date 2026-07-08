@extends('layouts.app')

@section('content')


<div class="card text-center border-info service-table-card">
        <div class="card-header border-info service-table-header">
            <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('services.index') }}">Clinic Services</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('services.create') }}">Add New Services</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('services.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
            </li>
            </ul>
        </div>

    <div class="card-body">
      <div class="input-group mb-4" style="margin:auto;max-width:300px">
        <form action="{{ route('services.archive_search') }}" method="POST">
          @csrf
          <div class="row">
            <div class="input-group-prepend">
              <input type="search" name="search" placeholder="Search for User " class="form-control">
              <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
              <a href="{{ route('services.archive') }}" class="form-control col-sm-3">Clear</a>
            </div>
          </div>
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
        <div class="service-table-wrap table-responsive-shell">
          <table class="table table-bordered table-hover service-data-table service-compact-table data-table">
            <thead class="thead-light">
              
              <tr>
                <th scope="col">NAME OF SERVICE</th>
                <th scope="col">DESCRIPTION</th>
                <th scope="col">ACTION</th>
              </tr>
            </thead>

            <tbody class="p2 text-center" id="myTable">
                @foreach($services as $service)
                <tr>
                  <td>{{ $service->name }}</td>
                  <td class="truncate-cell" title="{{ $service->description }}">{{ $service->description }}</td>
                  <td class="action-cell">
                    <form action="{{ route('services.destroy', $service->id) }}" method="post" class="table-action-group">
                      @csrf
                      @method('DELETE')
                      <a href="{{ route('services.show', $service->id) }}" class="table-action-button" title="View Service"><i class="fa fa-eye" aria-hidden="true"></i></a>
                      <a href="{{ route('services.edit', $service->id) }}" class="table-action-button" title="Edit Service"><i class="fa fa-edit" aria-hidden="true"></i></a>
                      <button class="table-action-button table-action-danger btn" type="submit" title="Archive Service" data-confirm="Archive {{ $service->name }}?" data-confirm-title="Archive service">
                        <i class="fa fa-archive" aria-hidden="true"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
            </tbody>
          </table>
        </div><br>
          <div class="pagination justify-content-center">
            {{$services->links()}}
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
