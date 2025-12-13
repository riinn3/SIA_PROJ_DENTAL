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
    public function index(Request $request)
    {
        // 1. Filter Parameters
        $status = $request->get('status', 'pending');
        $search = $request->get('search');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $date = $request->get('date'); // New parameter for single date filter

        // 2. Sorting Parameters (New Logic)
        // Default sort: Appointment Date, Ascending (Nearest to Far)
        $sort = $request->get('sort', 'appointment_date'); 
        $direction = $request->get('direction', 'asc');

        // Whitelist allowed sort columns to prevent errors/injection
        $allowedSorts = ['appointment_date', 'appointment_time', 'created_at', 'id'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'appointment_date';
        }

        // 3. Build Query
        $query = Appointment::with(['patient', 'doctor', 'service']);

        if ($date) {
            $query->whereDate('appointment_date', $date);
        } else {
            $query->where('status', $status);
            if ($startDate && $endDate) {
                $query->whereBetween('appointment_date', [$startDate, $endDate]);
            }
        }
        
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

        // 4. Apply Sorting
        $query->orderBy($sort, $direction);
        
        // Secondary sort for cleaner list (time)
        if ($sort == 'appointment_date') {
            $query->orderBy('appointment_time', $direction);
        }

        $appointments = $query->paginate(10)->withQueryString();

        $currentTab = $date ? 'today' : $status;

        // Pass sort/direction variables to view so the links work
        return view('admin.appointments.index', compact('appointments', 'status', 'search', 'startDate', 'endDate', 'sort', 'direction', 'date', 'currentTab'));
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
    public function confirm(Request $request, $id)
    {
        $appt = Appointment::findOrFail($id);
        $appt->update(['status' => 'confirmed']);

        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment confirmed.');
    }

    // --- 5. COMPLETE ACTION (Revenue Recording) ---
    public function complete(Request $request, $id) // Added Request $request
    {
        $appt = Appointment::findOrFail($id);
        $appointmentDateTime = $appt->appointment_date->setTimeFromTimeString($appt->appointment_time);

        if ($appointmentDateTime->isFuture()) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot mark future appointments as completed.');
        }

        $appt->update(['status' => 'completed']);
        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment marked as completed.');
    }

    // --- 6. CANCEL ACTION (With Audit Log) ---
    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string|max:255']);

        $appt = Appointment::findOrFail($id);

        if ($appt->status === 'completed') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Cannot cancel a completed appointment.');
        }
        
        $appt->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancelled_by' => Auth::id(), // Tracks WHO clicked the button
            'cancelled_at' => now(),      // Tracks WHEN
        ]);

        return redirect()->route('admin.appointments.index', $request->all())->with('success', 'Appointment cancelled.');
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

    // --- 8. EDIT APPOINTMENT ---
    public function edit($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'service'])->findOrFail($id);
        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();
        $services = Service::all();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'doctors', 'services'));
    }

    // --- 9. UPDATE APPOINTMENT ---
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today', // Cannot edit to past date
                'before_or_equal:' . \Carbon\Carbon::now()->addMonths(2)->endOfMonth()->toDateString(), // 2 months limit
            ],
            'appointment_time' => [
                'required',
                // Custom rule: if date is today, then time must be in future (relative to now)
                function ($attribute, $value, $fail) use ($request) {
                    $appointmentDateTime = \Carbon\Carbon::parse($request->appointment_date . ' ' . $value);
                    if ($appointmentDateTime->isToday() && $appointmentDateTime->lt(\Carbon\Carbon::now())) {
                        $fail('The ' . $attribute . ' must be a future time for today\'s appointments.');
                    }
                },
            ],
            'duration_minutes' => 'required|integer|min:30',
        ]);

        $requestedDate = Carbon::parse($request->appointment_date);
        $requestedStartTime = Carbon::parse($request->appointment_time);
        $durationMinutes = (int) $request->duration_minutes; // Explicitly cast to integer
        $requestedEndTime = $requestedStartTime->copy()->addMinutes($durationMinutes);
        
        // --- 1. Check Doctor's Schedule ---
        $schedule = \App\Models\Schedule::where('doctor_id', $request->doctor_id)
            ->where('date', $requestedDate)
            ->first();

        // Implement virtual schedule for Mon-Sat 09:00-17:00 if no explicit schedule exists
        if (!$schedule) {
            if ($requestedDate->isSunday()) {
                return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Doctor is not available on Sundays.');
            }
            // Use virtual schedule: 09:00-17:00, max_appointments: 20
            $schedule = new \App\Models\Schedule([
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'max_appointments' => 20
            ]);
        }

        $scheduleStartTime = Carbon::parse($schedule->start_time);
        $scheduleEndTime = Carbon::parse($schedule->end_time);

        // Check if doctor is on day off (00:00 - 00:00)
        if ($scheduleStartTime->format('H:i') === '00:00' && $scheduleEndTime->format('H:i') === '00:00') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Doctor is on a day off.');
        }

        // Check if appointment falls within doctor's working hours
        if ($requestedStartTime->lt($scheduleStartTime) || $requestedEndTime->gt($scheduleEndTime)) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Appointment is outside doctor\'s working hours (' . $scheduleStartTime->format('h:i A') . ' - ' . $scheduleEndTime->format('h:i A') . ').');
        }

        // --- 2. Check Max Appointments ---
        $currentAppointmentsCount = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $requestedDate)
            ->where('status', '!=', 'cancelled')
            ->where('id', '!=', $appointment->id) // Exclude current appointment
            ->count();

        if ($currentAppointmentsCount >= $schedule->max_appointments) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Doctor\'s schedule is full for this day.');
        }

        // --- 3. Check for Overlapping Appointments ---
        $overlap = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $requestedDate)
            ->where('id', '!=', $appointment->id) // Exclude current appointment
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($requestedStartTime, $requestedEndTime) {
                $query->where(function($q) use ($requestedStartTime, $requestedEndTime) {
                    $q->where('appointment_time', '<', $requestedEndTime->format('H:i:s'))
                      ->whereRaw('ADDTIME(appointment_time, SEC_TO_TIME(duration_minutes * 60)) > ?', [$requestedStartTime->format('H:i:s')]);
                });
            })
            ->count();

        if ($overlap > 0) {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Appointment time overlaps with an existing appointment.');
        }

        $appointment->update($request->all());

        return redirect()->route('admin.appointments.index', $request->all())
            ->with('success', 'Appointment updated successfully.');
    }

    // --- 10. RESTORE CANCELLED APPOINTMENT ---
    public function restore(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'cancelled') {
            return redirect()->route('admin.appointments.index', $request->all())->with('error', 'Only cancelled appointments can be restored.');
        }

        // Restore to pending status, clear cancellation details
        $appointment->update([
            'status' => 'pending',
            'cancellation_reason' => null,
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);

        // Optional: Trigger re-evaluation of schedule/slot availability here if needed
        // For simplicity, we assume restoring to pending means it will be manually re-confirmed
        // or re-checked against schedule by an admin.

        return redirect()->route('admin.appointments.index', array_merge($request->query(), ['status' => 'pending']))
            ->with('success', 'Appointment restored successfully to pending status.');
    }

    // --- 11. BLOCK SLOT (Ajax for Calendar) ---
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