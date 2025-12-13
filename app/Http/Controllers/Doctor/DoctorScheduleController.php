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
    public function index()
    {
        return view('doctor.schedule.index');
    }

    public function updateDateSchedule(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'is_day_off' => 'boolean'
        ]);

        $doctorId = Auth::id();
        
        // Prevent day off if bookings exist
        if ($request->is_day_off) {
            $bookedCount = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $request->date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            if ($bookedCount > 0) {
                return response()->json(['error' => "Cannot close clinic. $bookedCount appointments exist."], 422);
            }
        }

        Schedule::updateOrCreate(
            ['doctor_id' => $doctorId, 'date' => $request->date],
            [
                'start_time' => $request->is_day_off ? '00:00' : $request->start_time,
                'end_time' => $request->is_day_off ? '00:00' : $request->end_time,
                'max_appointments' => 99, 
            ]
        );

        return response()->json(['success' => 'Schedule updated.']);
    }
}