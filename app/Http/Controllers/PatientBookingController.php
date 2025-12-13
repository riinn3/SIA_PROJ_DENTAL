<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;

class PatientBookingController extends Controller
{
    // LOAD THE SINGLE PAGE WIZARD
    public function create()
    {
        $services = Service::orderBy('name')->get();
        // Load doctors with their schedules to (optionally) filter later
        $doctors = User::where('role', 'doctor')->get();

        return view('patient.booking.index', compact('services', 'doctors'));
    }

    // HANDLE THE SUBMISSION
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_date' => [
                'required',
                'date',
                'after_or_equal:today',
                'before_or_equal:' . \Carbon\Carbon::now()->addMonths(2)->endOfMonth()->toDateString(),
            ],
            'appointment_time' => [
                'required',
                // Custom rule: if date is today, then time must be in future
                function ($attribute, $value, $fail) use ($request) {
                    $appointmentDateTime = \Carbon\Carbon::parse($request->appointment_date . ' ' . $value);
                    if ($appointmentDateTime->isToday() && $appointmentDateTime->lt(\Carbon\Carbon::now())) {
                        $fail('The ' . $attribute . ' must be a future time for today\'s appointments.');
                    }
                },
            ],
        ]); // Added semicolon

        $service = Service::findOrFail($request->service_id);

        // Optional: Add backend conflict check here (same as Admin side)

        Appointment::create([
            'user_id' => Auth::id(),
            'doctor_id' => $request->doctor_id,
            'service_id' => $request->service_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'duration_minutes' => $service->duration_minutes,
            'status' => 'pending' // Online bookings need confirmation
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Request submitted! We will confirm your appointment shortly.');
    }
}