<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\ScheduleService; // Import the service

class CalendarController extends Controller
{
    protected $scheduleService; // Declare the property

    public function __construct(ScheduleService $scheduleService) // Inject the service
    {
        $this->scheduleService = $scheduleService;
    }

    // 1. CALENDAR EVENTS
    public function getEvents(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $doctorId = $request->doctor_id;

        // Fetch SAVED overrides
        $savedSchedules = Schedule::whereBetween('date', [$start, $end])
            ->where('doctor_id', $doctorId)
            ->get()
            ->keyBy(function($item) { return $item->date->format('Y-m-d'); });

        $events = [];
        $isPatient = Auth::check() && Auth::user()->role === 'patient';

        // Iterate through every day in the range to generate Virtual or Real events
        $curr = $start->copy();
        while ($curr <= $end) {
            $dateStr = $curr->format('Y-m-d');
            
            // Use ScheduleService to get the schedule (real or virtual)
            $actualSchedule = $this->scheduleService->getDoctorSchedule($dateStr, $doctorId);

            if (!$actualSchedule) { // If no actual or virtual schedule (e.g., Sunday)
                $curr->addDay();
                continue; // Skip and don't add an event
            }

            $startT = Carbon::parse($actualSchedule->start_time);
            $endT = Carbon::parse($actualSchedule->end_time);
            $isDayOff = $startT->format('H:i') === '00:00' && $endT->format('H:i') === '00:00';

            $title = '';
            $color = '';
            $textColor = '';
            $id = $savedSchedules->get($dateStr)->id ?? 'virtual-' . $dateStr; // Use actual schedule ID if exists

            if ($isDayOff) {
                $title = "DAY OFF";
                $color = '#e74a3b';
                $textColor = '#ffffff';
            } elseif ($isPatient) {
                $title = "Open";
                $color = '#1cc88a';
                $textColor = '#ffffff';
            } else {
                $count = Appointment::whereDate('appointment_date', $dateStr)
                    ->where('doctor_id', $doctorId)
                    ->where('status', '!=', 'cancelled')
                    ->where('status', '!=', 'blocked') // Exclude blocked slots from count
                    ->count();
                
                // SKIP IF 0 PATIENTS (Don't clutter calendar)
                if ($count == 0 && !$isDayOff) {
                    $curr->addDay();
                    continue;
                }

                $title = "$count Patient(s)";
                $color = '#ffffff';
                $textColor = '#4e73df';
            }

            $events[] = [
                'id' => $id,
                'title' => $title,
                'start' => $dateStr,
                'backgroundColor' => $color,
                'borderColor' => $isPatient ? $color : '#4e73df',
                'textColor' => $textColor,
                'extendedProps' => [
                    'start_time' => $actualSchedule->start_time,
                    'end_time' => $actualSchedule->end_time,
                ]
            ];
            $curr->addDay();
        }

        return response()->json($events);
    }

    // 2. SLOT DETAILS
    public function getDayDetails(Request $request)
    {
        $date = $request->date;
        $doctorId = $request->doctor_id;
        $durationMinutes = $request->duration_minutes ?? 30; // Default to 30 min if not provided
        $excludeAppointmentId = $request->exclude_appointment_id ?? null;

        $slotsData = $this->scheduleService->generateTimeSlots($date, $doctorId, (int)$durationMinutes, (int)$excludeAppointmentId);

        return response()->json($slotsData);
    }
}