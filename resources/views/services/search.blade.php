@extends('layouts.app')

@section('content')


<div class="card text-center border-info">
        <div class="card-header border-info">
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
      <div class="input-group mb-4" style="margin:auto;max-width:300px">
        <form action="{{ route('services.search') }}" method="POST">
          @csrf
          <div class="row">
            <div class="input-group-prepend">
              <input type="search" name="search" placeholder="Search for User " class="form-control">
              <button type="submit" class="form-control col-sm-2"><i class="fa fa-search"></i></button>
              <a href="{{ route('services.index') }}" class="form-control col-sm-3">Clear</a>
            </div>
          </div>
        </form>
      {{--<i class="fa fa-search"></i>--}}
        
      </div>
        <table class="table table-bordered table-responsive-md table-hover">
            <thead class="text-center thead-light">
              
              <tr>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Action</th>
              </tr>
            </thead>

            <tbody class="p2 text-center" id="myTable">
                @foreach($services as $service)
                <tr>
                  <td class="text-left">{{ $service->name }}</td>
                  <td>{{ $service->description }}</td>
                  <td>
                    <form action="{{ route('services.destroy', $service->id) }}" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                      @csrf
                      @method('DELETE')
                      <a href="{{ route('services.show', $service->id) }}"><i class="fa fa-eye" style="padding-right:20px"aria-hidden="true"></a></i>
                      <a href="{{ route('services.edit', $service->id) }}"><i class="fa fa-edit" style="padding-right:20px"aria-hidden="true"></a></i>
                      <button class="btn" type="submit">
                        <i class="fa fa-archive" data-toggle="tooltip" data-placement="top" title="archive" style="padding-right:15px"aria-hidden="true"></i> 
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
            </tbody>
          </table><br>
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