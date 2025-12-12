<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Schedule;
use Carbon\Carbon;

class DoctorController extends Controller
{
    public function dashboard()
    {
        $doctorId = Auth::id();
        $today = Carbon::today();

        // 1. Stats
        $todayCount = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        $totalTreated = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->count();

        $upcomingScheduleCount = Schedule::where('doctor_id', $doctorId)
            ->where('date', '>=', $today)
            ->count();

        // 2. Queue
        $todayAppointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_time', 'asc')
            ->get();

        return view('doctor.dashboard', compact(
            'todayCount', 'totalTreated', 'upcomingScheduleCount', 'todayAppointments'
        ));
    }

    public function mySchedule()
    {
        $schedules = Schedule::where('doctor_id', Auth::id())
            ->where('date', '>=', Carbon::today())
            ->orderBy('date', 'asc')
            ->paginate(10);
        return view('doctor.schedule.index', compact('schedules'));
    }
}