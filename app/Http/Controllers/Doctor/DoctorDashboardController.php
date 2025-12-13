<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

class DoctorDashboardController extends Controller
{
    /**
     * Show the Doctor's Dashboard (Agenda).
     */
    public function index()
    {
        $doctorId = Auth::id();
        $today = Carbon::today();

        // 1. Get Today's Appointments (All)
        $todaysAppointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending']) 
            ->orderBy('appointment_time')
            ->get();

        // 2. Upcoming List (Next 7 Days)
        $upcomingAppointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', '>', $today)
            ->where('appointment_date', '<=', $today->copy()->addDays(7))
            ->where('status', 'confirmed')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
            
        $upcomingCount = $upcomingAppointments->count();

        // 3. IDENTIFY "UP NEXT" (First confirmed, not completed)
        // We look for the first one that is 'confirmed' and time is >= now (or just the first confirmed in the sorted list that isn't completed)
        // Simplification: Just take the first 'confirmed' one from today's list that matches time
        $nextPatient = $todaysAppointments->where('status', 'confirmed')->first(); 
        
        // 4. Get Today's Working Hours
        $schedule = \App\Models\Schedule::where('doctor_id', $doctorId)
            ->where('date', $today)
            ->first();

        return view('doctor.dashboard', compact('todaysAppointments', 'upcomingCount', 'upcomingAppointments', 'nextPatient', 'schedule'));
    }

    /**
     * Show list of recent appointments for diagnosis/notes.
     */
    public function recentConsultations()
    {
        // Fetch appointments that are completed (past interactions)
        $appointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', Auth::id())
            ->where('status', 'completed')
            ->orderByDesc('appointment_date') // Newest first
            ->orderByDesc('appointment_time')
            ->paginate(10);

        return view('doctor.consultations', compact('appointments'));
    }

    /**
     * Save Medical Notes / Diagnosis.
     */
    public function updateDiagnosis(Request $request, $id)
    {
        $request->validate([
            'diagnosis' => 'required|string|min:5',
            'prescription' => 'nullable|string'
        ]);

        $appt = Appointment::where('id', $id)
            ->where('doctor_id', Auth::id())
            ->firstOrFail();

        $appt->update([
            'diagnosis' => $request->diagnosis,
            'prescription' => $request->prescription,
            'status' => 'completed' // Auto-complete the appointment when notes are added
        ]);

        return back()->with('success', 'Consultation notes saved successfully.');
    }
}