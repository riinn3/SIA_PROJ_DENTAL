<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // --- 1. ACTIVE LIST ---
    public function index()
    {
        $services = Service::orderBy('created_at', 'desc')->get();
        return view('admin.services.index', compact('services'));
    }

    // --- 2. CREATE ---
    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        // 1. validate and capture the clean data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
            'description' => 'nullable|string', // added this so description is allowed
        ]);

        // 2. create using only the validated data (ignores _token automatically)
        Service::create($validatedData);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service added successfully!');
    }

    // --- 3. EDIT & UPDATE ---
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully!');
    }

    // --- 4. ARCHIVE (Soft Delete) ---
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete(); // Soft delete

        return redirect()->route('admin.services.index')
            ->with('success', 'Service archived.');
    }

    // --- 5. ARCHIVES VIEW (The Recycle Bin) ---
    public function archives()
    {
        // Get ONLY the soft-deleted items
        $archivedServices = Service::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        return view('admin.services.archives', compact('archivedServices'));
    }

    // --- 6. RESTORE ---
    public function restore($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        $service->restore();

        return redirect()->route('admin.services.archives')
            ->with('success', 'Service restored to active list.');
    }

    // --- 7. PERMANENT DELETE ---
    public function forceDelete($id)
    {
        $service = Service::onlyTrashed()->findOrFail($id);
        
        // Safety: In a real app, check if appointment history exists before hard deleting
        // For now, we allow it.
        $service->forceDelete();

        return redirect()->route('admin.services.archives')
            ->with('success', 'Service permanently deleted.');
    }
}