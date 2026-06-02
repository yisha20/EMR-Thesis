@extends('layouts.app')

@section('content')
<div class="card text-center border-info">
    <div class="card-header border-info">
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
        <table class="table table-bordereds table-responsive-md table-hover">
            <thead class="text-center thead-light">
              <tr>
                <th scope="col">Name of Service</th>
                <th scope="col">Description</th>
                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody id="myTable">
              @foreach($services as $service)
              <tr>
                <td>{{ $service->name }}</td>
                <td>{{ $service->description }}</td>
                <td>
                  <form action="{{ route('services.delete', $service->id) }}" id="deleteForm" onsubmit="return confirmDelete()" method="post">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('services.restore', $service->id) }}"><i class="fa fa-refresh" style="padding-right:20px"aria-hidden="true"></a></i>
                    <button type="submit" class="btn">
                        <i class="fa fa-trash" style="padding-right:15px"aria-hidden="true"></i> 
                    </button>
                  </form>
                </td>
              </tr>
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

