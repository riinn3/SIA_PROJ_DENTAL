<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    // 1. CALENDAR EVENTS (Dots/Colors)
    public function getEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $doctorId = $request->doctor_id;

        $schedules = Schedule::whereBetween('date', [$start, $end])
            ->where('doctor_id', $doctorId)
            ->get();

        $events = [];
        $isPatient = Auth::check() && Auth::user()->role === 'patient';

        foreach ($schedules as $sched) {
            // Patient View: Always Green "Available" if scheduled
            // Admin View: Shows count
            if ($isPatient) {
                $title = "Open";
                $color = '#1cc88a'; // Green
                $textColor = '#ffffff';
            } else {
                $count = Appointment::whereDate('appointment_date', $sched->date)
                    ->where('doctor_id', $doctorId)->where('status', '!=', 'cancelled')->count();
                $title = "$count Patient(s)";
                $color = '#ffffff';
                $textColor = '#4e73df';
            }

            $events[] = [
                'id' => $sched->id,
                'title' => $title,
                'start' => $sched->date->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $isPatient ? $color : '#4e73df',
                'textColor' => $textColor
            ];
        }

        return response()->json($events);
    }

    // 2. SLOT DETAILS (Strict Blocking)
    public function getDayDetails(Request $request)
    {
        $date = $request->date;
        $doctorId = $request->doctor_id;

        // 1. Get Schedule
        $schedule = Schedule::whereDate('date', $date)->where('doctor_id', $doctorId)->first();
        if (!$schedule) return response()->json(['status' => 'closed', 'message' => 'Closed']);

        // 2. Get Bookings
        $bookings = Appointment::whereDate('appointment_date', $date)
            ->where('doctor_id', $doctorId)
            ->where('status', '!=', 'cancelled')
            ->get();

        // 3. Generate Slots
        $slots = [];
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        
        // Hardcoded Lunch (12:00 PM - 1:00 PM)
        $lunchStart = Carbon::parse($date . ' 12:00:00');
        $lunchEnd = Carbon::parse($date . ' 13:00:00');

        while ($startTime < $endTime) {
            $slotStart = $startTime->copy();
            $slotEnd = $startTime->copy()->addMinutes(30);
            $label = $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A');

            // STATUS DEFAULT: AVAILABLE
            $status = 'available';
            $details = 'Available';

            // CHECK 1: LUNCH (Strict)
            // If slot starts at 12:00 OR 12:30, it is lunch.
            if ($slotStart->betweenIncluded($lunchStart, $lunchEnd->copy()->subMinute())) {
                $status = 'lunch';
                $details = 'Lunch Break';
            }

            // CHECK 2: BOOKINGS (Overlap)
            foreach ($bookings as $appt) {
                $apptStart = Carbon::parse($appt->appointment_date->format('Y-m-d') . ' ' . $appt->appointment_time->format('H:i:s'));
                $apptEnd = $apptStart->copy()->addMinutes($appt->duration_minutes);

                // If Slot Start is inside the Appointment Duration
                if ($slotStart >= $apptStart && $slotStart < $apptEnd) {
                    $status = 'booked';
                    $details = 'Booked'; // Privacy: Don't show name
                    break; 
                }
            }

            $slots[] = [
                'time_label' => $label,
                'raw_time' => $slotStart->format('H:i'),
                'type' => $status,
                'details' => $details
            ];

            $startTime->addMinutes(30);
        }

        return response()->json(['status' => 'open', 'slots' => $slots]);
    }
}