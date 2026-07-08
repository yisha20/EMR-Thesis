@extends('layouts.app')

@section('content')
<div class="card text-center border-info service-table-card">
    <div class="card-header border-info service-table-header">
      <ul class="nav nav-tabs card-header-tabs">
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('services.index') }}">Clinic Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.create') }}">Add New Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('services.archive') }}">Archive</a>{{-- sir erik sudgested na walang delete. those student nga nag left sa school kay mabutang diri ilang medical records para maretrieve nila if kailanganin--}}
        </li>
      </ul>
      
    </div>
    <div class="card-body">
      <form action="{{ route('services.index') }}" method="GET" class="emr-filter-bar filter-toolbar">
        <div class="emr-filter-search filter-search">
          <i class="fa fa-search"></i>
          <input type="search" id="serviceSearch" placeholder="Search services" aria-label="Search services">
        </div>
        <select name="category" class="form-control filter-select" aria-label="Filter by category">
          <option value="">All categories</option>
          @foreach (['Consultation', 'Immunization', 'Treatment', 'Laboratory', 'First Aid'] as $category)
            <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
          @endforeach
        </select>
        <div class="filter-actions">
          <button class="btn btn-primary" type="submit">Filter</button>
          <a class="btn btn-light" href="{{ route('services.index') }}">Reset</a>
        </div>
      </form>
      <div class="service-table-wrap table-responsive-shell">
        <table class="table table-bordered table-hover service-data-table data-table">
            <thead class="thead-light">
              <tr>
                <th>NAME OF SERVICE</th>
                <th>CATEGORY</th>
                <th>DESCRIPTION</th>
                <th>STATUS</th>
                <th>ACTION</th>
              </tr>
            </thead>
            <tbody id="myTable">
              @forelse($services as $service)
              <tr>
                <td>{{ $service->name }}</td>
                <td><span class="service-category-badge">{{ $service->category }}</span></td>
                <td class="truncate-cell" title="{{ $service->description }}">{{ $service->description }}</td>
                <td><span class="emr-status-badge {{ strtolower($service->status) }}">{{ $service->status }}</span></td>
                <td class="action-cell">
                  <form action="{{ route('services.destroy', $service->id) }}" method="post" class="table-action-group">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('services.show', $service->id) }}" class="table-action-button" aria-label="View service" title="View service" data-toggle="tooltip"><i class="fa fa-eye" aria-hidden="true"></i></a>
                    <a href="{{ route('services.edit', $service->id) }}" class="table-action-button" aria-label="Edit service" title="Edit service" data-toggle="tooltip"><i class="fa fa-edit" aria-hidden="true"></i></a>
                    <button class="table-action-button table-action-danger btn" type="submit" aria-label="Archive service" title="Archive service" data-toggle="tooltip" data-confirm="Archive {{ $service->name }}?" data-confirm-title="Archive service">
                      <i class="fa fa-archive" aria-hidden="true"></i>
                    </button>
                  </form>
                </td>
              </tr>
              @empty
              <tr><td colspan="5">@include('includes.empty-state', ['title' => 'No services found.', 'message' => 'Add a service or change the category filter.', 'icon' => 'fa-medkit'])</td></tr>
              @endforelse
            </tbody>
        
          </table>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function(){
      $("#serviceSearch").on("keyup", function() {
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
