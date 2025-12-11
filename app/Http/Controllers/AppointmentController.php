<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // --- 1. LIST APPOINTMENTS (Admin Transaction View) ---
    public function index(Request $request)
    {
        // A. Filters
        $status = $request->get('status', 'pending'); // Default tab
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // B. Base Query (Eager load relationships for performance)
        $query = Appointment::with(['patient', 'doctor', 'service'])
            ->where('status', $status);

        // C. Date Filter
        if ($startDate && $endDate) {
            $query->whereBetween('appointment_date', [$startDate, $endDate]);
        }

        // D. Search Logic (Find by Patient Name or Doctor Name)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('patient', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        // E. Sorting
        // Pending/Confirmed: Show nearest upcoming first (Ascending)
        // Completed/Cancelled: Show most recent past first (Descending)
        if (in_array($status, ['pending', 'confirmed'])) {
            $query->orderBy('appointment_date', 'asc')->orderBy('appointment_time', 'asc');
        } else {
            $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'desc');
        }

        // F. Pagination
        $appointments = $query->paginate(10)->withQueryString();

        return view('admin.appointments.index', compact('appointments', 'status', 'search', 'startDate', 'endDate'));
    }

    // --- 2. SHOW WALK-IN FORM ---
    public function create()
    {
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.create', compact('patients', 'doctors', 'services'));
    }

    // --- 3. STORE (Saves MINUTES now) ---
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            // CRITICAL CHANGE: Validating minutes (min 30)
            'duration_minutes' => 'required|integer|min:30', 
        ]);

        // (Optional: You can re-add conflict checking here if you want extra safety)
        
        Appointment::create([
            'user_id' => $request->user_id,
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_minutes' => $request->duration_minutes, // Saving Minutes
            'status' => 'confirmed' // Walk-ins are instantly confirmed
        ]);

        return redirect()->route('admin.appointments.index', ['status' => 'confirmed'])
            ->with('success', 'Appointment booked successfully.');
    }

    // --- 4. CONFIRM ACTION ---
    public function confirm($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return back()->with('success', 'Appointment confirmed.');
    }

    // --- 5. COMPLETE ACTION (Revenue Recording) ---
    public function complete($id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'completed']);

        return back()->with('success', 'Appointment marked as completed.');
    }

    // --- 6. CANCEL ACTION (With Audit Log) ---
    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);

        $appt = Appointment::findOrFail($id);
        
        $appt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(), // Tracks WHO clicked the button
            'cancelled_at' => now(),      // Tracks WHEN
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    // --- 7. SHOW DETAILS ---
    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service', 'canceller'])->findOrFail($id);
        
        if($appointment->status === 'blocked') {
            return back()->with('info', 'This is a manual administrative block.');
        }

        return view('admin.appointments.show', compact('appointment'));
    }

    // --- 8. BLOCK SLOT (Ajax for Calendar) ---
    public function blockSlot(Request $request)
    {
        if ($request->status === 'reserved') {
            Appointment::create([
                'doctor_id' => $request->doctor_id,
                'appointment_date' => $request->date,
                'appointment_time' => $request->time,
                'status' => 'blocked', 
                'duration_minutes' => 60, // Default manual block is 1 hour
                'service_id' => null,  
                'user_id' => null      
            ]);
        } else {
            // Unblock
            Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $request->time)
                ->where('status', 'blocked')
                ->delete(); 
        }
        return response()->json(['success' => true]);
    }
}