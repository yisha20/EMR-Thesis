<?php

namespace App\Http\Controllers;

use App\Helpers\ActivityLogger;
use App\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::query()
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->category);
            })
            ->orderBy('name')
            ->get();

        return view('services.index', [
            'services' => $services
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
            ->where(function ($query) use ($data) {
                $query->where('name', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('description', 'LIKE', '%' . $data['search'] . '%')
                    ->orWhere('added_by', 'LIKE', '%' . $data['search'] . '%');
            })
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
            'category' => 'required|in:Consultation,Immunization,Treatment,Laboratory,First Aid',
            'description' => 'required',
            'status' => 'required|in:Active,Inactive',
        ]);

        $service = Service::create([
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'status' => $request->status,
            'added_by' => auth()->id(),
        ]);

        ActivityLogger::log('added service (' . $service->name . ')');
        
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
            'category' => 'required|in:Consultation,Immunization,Treatment,Laboratory,First Aid',
            'description' => 'required',
            'status' => 'required|in:Active,Inactive',
        ]);

        $service->fill($request->only('name', 'category', 'description', 'status'))->save();

        ActivityLogger::log('updated service (' . $service->name . ')');
        
        return redirect()->back()->with('success', 'A service has been updated.');
    }

    public function destroy(Service $service)
    {
        $service->archived_by = auth()->id();
        $service->save();
        $service->delete();
        ActivityLogger::log('archived service (' . $service->name . ')');
        
        return redirect()
            ->back()
            ->with('success', 'A service has been archived.');
    }

    public function forceDestroy($id)
    {
        $service = Service::withTrashed()->where('id', $id)->first();
        $service->forceDelete();
        ActivityLogger::log('deleted service (' . $service->name . ')');
        
        return redirect()
            ->back()
            ->with('success', 'A service has been deleted permanently.');
    }

    public function restore($id)
    {
        $service = Service::withTrashed()->where('id', $id)->first();
        $service->restore();
        $service->archived_by = null;
        $service->save();
        ActivityLogger::log('restored service (' . $service->name . ')');
        
        return redirect()
            ->back()
            ->with('success', 'A service has been restored.');
    }
}
