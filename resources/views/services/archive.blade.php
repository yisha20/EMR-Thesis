@extends('layouts.app')

@section('content')
<div class="card text-center border-info service-table-card">
    <div class="card-header border-info service-table-header">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link " href="{{ route('services.index') }}">Clinic Services</a>
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
      <div class="input-group mb-4" style="margin:auto;max-width:250px">
        <form action="{{ route('services.archive_search') }}" method="POST">
          @csrf
            <div class="row">
                <div class="input-group-prepend">
                  <input type="search" autocomplete="off" name="search" placeholder="Search for Services" class="form-control">
                  <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
                </div>
            </div> 
        </form> 
    </div>
        <div class="service-table-wrap table-responsive-shell">
          <table class="table table-bordereds table-hover service-data-table data-table is-wide">
            <thead class="thead-light">
              <tr>
                <th scope="col">NAME OF SERVICE</th>
                <th scope="col">CATEGORY</th>
                <th scope="col">DESCRIPTION</th>
                <th scope="col">ARCHIVE DATE</th>
                <th scope="col">ARCHIVED BY</th>
                <th scope="col">ACTION</th>
              </tr>
            </thead>
            <tbody id="myTable">
              @foreach($services as $service)
              <tr>
                <td>{{ $service->name }}</td>
                <td><span class="service-category-badge">{{ $service->category }}</span></td>
                <td class="truncate-cell" title="{{ $service->description }}">{{ $service->description }}</td>
                <td>{{ optional($service->deleted_at)->format('M j, Y g:i A') }}</td>
                <td>{{ optional($service->archivedBy)->fullName() ?: 'Unknown' }}</td>
                <td class="action-cell">
                  <form action="{{ route('services.delete', $service->id) }}" method="post" class="table-action-group">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('services.restore', $service->id) }}" class="table-action-button" title="Restore service" aria-label="Restore service" data-toggle="tooltip" data-confirm="Restore {{ $service->name }}?" data-confirm-title="Restore service"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                    <button type="submit" class="table-action-button table-action-danger btn" title="Delete service" aria-label="Delete service" data-toggle="tooltip" data-confirm="Permanently delete {{ $service->name }}? This cannot be undone." data-confirm-title="Delete service">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
        
          </table>
        </div>
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

@stop

{{--@extends('layouts.app')
@section('content')
    Index
    <br>
    <a href="{{ route('services.show', 1) }}">Go to Show page with ID 1</a>
    <br>
    <a href="{{ route('services.edit', 1) }}">Go to Edit page with ID 1</a>
    <br>
    <a href="{{ route('services.create') }}">Go to Create page</a>
@endsection
--}}
