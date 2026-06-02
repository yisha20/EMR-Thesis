<?php

namespace App\Http\Controllers;

use App\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('services.index', [
            'services' => Service::get()
        ]);
    }

    public function archive()
    {
        return view('services.archive', [
            'services' => Service::withTrashed()
                ->where('deleted_at', '!=', null)
                ->get()
        ]);
    }

    public function show(Service $service)
    {
        return view('services.show', [
            'service' => $service
        ]);
    }

     /**
     * Searches for a services from DB.
     * 
     * @return Collection
     */
    public function search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);
        
        $services = Service::where('name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('description', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('added_by', 'LIKE', '%' . $data['search'] . '%')
          
            ->paginate(20);

        return view('services.search', [
            'services' => $services
        ]);
    }

    /**
     * Searches for a services from DB.
     * 
     * @return Collection
     */
    public function archive_search()
    {
        $data = request()->validate([
            'search' => 'required',
        ]);
        
        $services = Service::onlyTrashed()
            ->where('name', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('description', 'LIKE', '%' . $data['search'] . '%')
            ->orWhere('added_by', 'LIKE', '%' . $data['search'] . '%')
            ->paginate(20);

        return view('services.archive_search', [
            'services' => $services 
        ]);
    }




    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        Service::create($request->only( 
            'name',
            'description',
            'added_by'
            )
        );
        
        return redirect()
            ->back()
            ->with('success', 'A new service has been added.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', [
            'service' => $service
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
        ]);

        $service->fill($request->only(
                'name',
                'description',
                'added_by'
            )
        )->save();
        
        return redirect()->back()->with('success', 'A service has been updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        
        return redirect()
            ->back()
            ->with('success', 'A service has been archived.');
    }

    public function forceDestroy($id)
    {
        $service = Service::withTrashed()->where('id', $id)->first();
        $service->forceDelete();
        
        return redirect()
            ->back()
            ->with('success', 'A service has been deleted permanently.');
    }

    public function restore($id)
    {
        $service = Service::withTrashed()->where('id', $id)->first();
        $service->restore();
        
        return redirect()
            ->back()
            ->with('success', 'A service has been restored.');
    }
}

