<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\Appointment;
use App\Models\User;

class CalendarController extends Controller
{
    public function getEvents(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $doctorId = $request->doctor_id; // <--- Get the ID from the dropdown

        $query = Schedule::whereBetween('date', [$start, $end]);

        // If a specific doctor is selected, filter by them.
        // If no doctor is selected (value=""), maybe show ALL or NONE. 
        // Let's show ALL by default, or filter if ID is present.
        if ($doctorId) {
            $query->where('doctor_id', $doctorId);
        }

        $schedules = $query->get();
        // 1. Get the range (FullCalendar sends 'start' and 'end' dates)
        $start = $request->start;
        $end = $request->end;
        $doctorId = $request->doctor_id; // Filter by doctor

        // 2. Fetch Doctor Schedules in that range
        $query = Schedule::whereBetween('date', [$start, $end]);
        
        // (Optional: If you add 'doctor_id' to schedules table later)
        // if ($doctorId) { $query->where('doctor_id', $doctorId); }

        $schedules = $query->get();

        $events = [];

        foreach ($schedules as $sched) {
            // 3. Count Appointments for this day
            $bookedCount = Appointment::whereDate('appointment_date', $sched->date)
                ->where('status', '!=', 'cancelled')
                ->count();

            // 4. Determine Color
            $color = '#1cc88a'; // Green (Default)
            $title = "Available";

            if ($bookedCount >= $sched->max_appointments) {
                $color = '#e74a3b'; // Red (Full)
                $title = "FULL";
            } elseif ($bookedCount >= ($sched->max_appointments / 2)) {
                $color = '#f6c23e'; // Yellow (Filling Up)
                $title = "Filling Up";
            }

            // 5. Format for FullCalendar
            $events[] = [
                'id' => $sched->id,
                'title' => $title . " ($bookedCount/$sched->max_appointments)",
                'start' => $sched->date->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'total' => $sched->max_appointments,
                    'booked' => $bookedCount,
                    'status' => $title
                ]
            ];
        }

        return response()->json($events);
    }

    // Get specific slots when a day is clicked
    public function getDayDetails(Request $request)
    {
        $date = $request->date;
        $doctorId = $request->doctor_id;

        // 1. Get the Schedule settings for this day/doctor
        $schedule = Schedule::whereDate('date', $date)
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('doctor_id', $doctorId);
            })
            ->first();

        if (!$schedule) {
            return response()->json(['status' => 'closed', 'message' => 'Doctor is not scheduled for this day.']);
        }

        // 2. Get existing appointments
        $appointments = Appointment::with(['patient', 'service'])
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($doctorId, function($q) use ($doctorId) {
                return $q->where('doctor_id', $doctorId);
            })
            ->get();

        // 3. Generate Slots (Hourly)
        $slots = [];
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);

        // Loop hour by hour
        while ($startTime < $endTime) {
            $slotTime = $startTime->format('H:i:s');
            $displayTime = $startTime->format('h:i A');
            
            // Check if this slot is taken
            $booking = $appointments->first(function ($appt) use ($slotTime) {
                // strict check: does appointment start exactly at this time?
                return Carbon::parse($appt->appointment_time)->format('H:i:s') === $slotTime;
            });

            if ($booking) {
                $slots[] = [
                    'time' => $displayTime,
                    'raw_time' => $slotTime,
                    'status' => 'booked',
                    'info' => $booking // pass patient details
                ];
            } else {
                $slots[] = [
                    'time' => $displayTime,
                    'raw_time' => $slotTime,
                    'status' => 'available',
                    'doctor_id' => $schedule->doctor_id // needed for booking
                ];
            }

            $startTime->addHour(); // increment by 1 hour
        }

        return response()->json(['status' => 'open', 'slots' => $slots]);
    }
}