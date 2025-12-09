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

    // 7. STORE FROM CALENDAR MODAL (Walk-In)
    public function storeWalkIn(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'duration' => 'required|integer|min:1',
            'patient_type' => 'required|in:existing,new',
        ]);

        // 1. Handle Patient (Get ID or Create New)
        $patientId = null;

        if ($request->patient_type === 'new') {
            $request->validate([
                'new_name' => 'required|string',
                'new_phone' => 'required|string',
                // Email is optional for walk-ins, but we need a unique placeholder if missing
                'new_email' => 'nullable|email|unique:users,email', 
            ]);

            // Create Guest User
            $newUser = User::create([
                'name' => $request->new_name,
                'email' => $request->new_email ?? 'guest_'.time().'@clinic.com', // Placeholder if empty
                'phone' => $request->new_phone,
                'password' => bcrypt('password'), // Default password
                'role' => 'patient',
                'email_verified_at' => now(), // Auto-verify walk-ins
            ]);
            $patientId = $newUser->id;
        } else {
            $request->validate(['user_id' => 'required|exists:users,id']);
            $patientId = $request->user_id;
        }

        // 2. Check for Overlaps (Duration Logic)
        // If duration is 2 hours, we must check if the NEXT hour is also free.
        // (For Capstone simplicity, we will skip complex overlap validation for now and just book it).

        // 3. Create Appointment
        Appointment::create([
            'user_id' => $patientId,
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'confirmed' // Walk-ins are always confirmed immediately
        ]);

        // 4. If duration > 1, create blocking slots? 
        // For simple implementations, we usually just let the calendar show "Booked" for the start time.
        // A pro version would create multiple appointment records or store 'end_time' in DB.
        
        return redirect()->back()->with('success', 'Appointment booked successfully!');
    }
}