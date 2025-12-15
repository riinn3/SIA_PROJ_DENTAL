<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    // 1. Show the Schedule Page
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $doctor_id = Auth::id();

        // A. Define working hours (e.g., 9 AM to 5 PM)
        $startTime = Carbon::parse($date . ' 09:00:00');
        $endTime   = Carbon::parse($date . ' 17:00:00');
        
        // B. Get all appointments/blocks for this date
        $existingBookings = Appointment::where('doctor_id', $doctor_id)
            ->whereDate('appointment_date', $date)
            ->get();

        $slots = [];

        // C. Generate 30-minute slots loop
        while ($startTime < $endTime) {
            $timeStr = $startTime->format('H:i:s');
            
            // Check if this specific time is blocked or booked
            $booking = $existingBookings->first(function($item) use ($timeStr) {
                return $item->appointment_time == $timeStr;
            });

            $status = 'available';
            if ($booking) {
                if ($booking->status == 'blocked') {
                    $status = 'blocked';
                } elseif (in_array($booking->status, ['confirmed', 'completed'])) {
                    $status = 'booked';
                }
            }

            $slots[] = [
                'time' => $timeStr,
                'display' => $startTime->format('h:i A'),
                'status' => $status,
                'booking_id' => $booking ? $booking->id : null // We need this to unblock (delete)
            ];

            $startTime->addMinutes(30);
        }

        return view('doctor.schedule.index', compact('date', 'slots'));
    }

    // 2. Initialize Day (The Missing Button Fix)
    public function updateDateSchedule(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'nullable' // 0 or 1
        ]);

        $isDayOff = filter_var($request->is_day_off, FILTER_VALIDATE_BOOLEAN);

        if ($isDayOff) {
            $start = '00:00:00';
            $end   = '00:00:00';
        } else {
            $start = $request->start_time ?: '09:00:00';
            $end   = $request->end_time   ?: '17:00:00';
        }

        Schedule::updateOrCreate(
            [
                'doctor_id' => Auth::id(), 
                'date' => $request->date
            ],
            [
                'start_time' => $start,
                'end_time' => $end,
                'max_appointments' => 20 
            ]
        );

        return response()->json(['message' => 'Schedule updated successfully!']);
    }

    // In app/Http/Controllers/Doctor/DoctorScheduleController.php

public function toggleSlot(Request $request)
{
    // 1. Validate
    $request->validate([
        'date' => 'required|date',
        'time' => 'required', // e.g., "09:00:00"
        'action' => 'required|in:block,unblock' // We use 'unblock' instead of 'open' for clarity
    ]);

    $doctorId = auth()->id();

    if ($request->action === 'block') {
        // 2. BLOCKING: Create a dummy appointment to fill the slot
        // Check if already blocked to prevent duplicates
        $exists = \App\Models\Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $request->date)
            ->where('appointment_time', $request->time)
            ->exists();

        if (!$exists) {
            \App\Models\Appointment::create([
                'doctor_id' => $doctorId,
                'user_id' => $doctorId, // The doctor "owns" this block
                'service_id' => 1,      // Ensure you have a service with ID 1, or make column nullable
                'appointment_date' => $request->date,
                'appointment_time' => $request->time,
                'duration_minutes' => 30, // Default slot duration
                'status' => 'blocked',    // This is the keyword our index searches for
                'price' => 0
            ]);
        }
        $msg = 'Slot has been blocked.';
    } else {
        // 3. UNBLOCKING: Find and delete the "blocked" appointment
        \App\Models\Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', $request->date)
            ->where('appointment_time', $request->time)
            ->where('status', 'blocked')
            ->delete();
            
        $msg = 'Slot is now open.';
    }

    // 4. RELOAD THE PAGE
    return back()->with('success', $msg);
}
}