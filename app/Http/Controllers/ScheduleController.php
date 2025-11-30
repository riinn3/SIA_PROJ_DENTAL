<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // 1. List all upcoming schedules
    public function index()
    {
        // Get all doctors to populate the filter dropdown
        $doctors = \App\Models\User::where('role', 'doctor')->get();
        
        return view('admin.schedules.index', compact('doctors'));
    }

    // 2. Show the form (Updated to accept pre-filled date)
    public function create(Request $request)
    {
        $prefilledDate = $request->get('date'); // Get date from URL if it exists
        return view('admin.schedules.create', compact('prefilledDate'));
    }

    // 3. Save the availability
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id', // <--- Must pick a doctor
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_appointments' => 'required|integer|min:1',
        ]);

        // Check duplicates for THAT SPECIFIC DOCTOR only
        $exists = Schedule::where('doctor_id', $request->doctor_id)
                          ->where('date', $request->date)
                          ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'This doctor already has a schedule for this date.']);
        }

        Schedule::create($request->all());

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Availability set successfully!');
    }

    // 4. Delete a schedule
    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        
        if ($schedule->booked_count > 0) {
            return back()->with('error', 'Cannot delete: Patients are already booked for this day.');
        }

        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Availability removed.');
    }
}