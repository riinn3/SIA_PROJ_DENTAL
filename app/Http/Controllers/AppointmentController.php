<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // 1. LIST APPOINTMENTS (Tabs Logic)
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending'); // Default to pending

        $appointments = Appointment::with(['patient', 'doctor', 'service'])
            ->where('status', $status)
            ->orderBy('appointment_date', 'asc')
            ->get();

        return view('admin.appointments.index', compact('appointments', 'status'));
    }

    // 2. SHOW WALK-IN FORM
    public function create()
    {
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.create', compact('patients', 'doctors', 'services'));
    }

    // 3. STORE WALK-IN (Instant Confirm)
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Create with 'confirmed' status immediately for walk-ins
        Appointment::create([
            'user_id' => $request->user_id,
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'confirmed'
        ]);

        return redirect()->route('admin.appointments.index', ['status' => 'confirmed'])
            ->with('success', 'Walk-in appointment booked successfully.');
    }

    // 4. CONFIRM (Pending -> Confirmed)
    public function confirm($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return back()->with('success', 'Appointment confirmed.');
    }

    // 5. CANCEL / VOID (With Reason)
    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);

        $appt = Appointment::findOrFail($id);
        
        $appt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    // 6. COMPLETE (Confirmed -> Completed)
    public function complete($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }
}