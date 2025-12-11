<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        // Check if user clicked the "Archived" tab
        $view = $request->get('view', 'active'); 

        if ($view === 'archived') {
            // UPDATED: paginate(10)
            $services = Service::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(10);
        } else {
            // UPDATED: paginate(10)
            $services = Service::orderBy('created_at', 'desc')->paginate(10);
        }

        return view('admin.services.index', compact('services', 'view'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // CHANGED: Minimum 30 mins, and must be a multiple of 30
            'duration_minutes' => 'required|integer|min:30|multiple_of:30', 
            'description' => 'nullable|string',
        ]);
        Service::create($data);
        return redirect()->route('admin.services.index')->with('success', 'Service added.');
    }

    // --- 3. EDIT & UPDATE ---
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            // CHANGED: Enforce 30-minute blocks
            'duration_minutes' => 'required|integer|min:30|multiple_of:30',
        ]);
        Service::findOrFail($id)->update($request->all());
        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    // SOFT DELETE
    public function destroy($id)
    {
        Service::findOrFail($id)->delete();
        return back()->with('success', 'Service archived.');
    }

    // RESTORE
    public function restore($id)
    {
        Service::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Service restored.');
    }

    // PERMANENT DELETE
    public function forceDelete($id)
    {
        Service::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Service permanently deleted.');
    }
}