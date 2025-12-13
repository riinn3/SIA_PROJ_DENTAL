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

        // 1. Get Today's Appointments
        $todaysAppointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending']) 
            ->orderBy('appointment_time')
            ->get();

        // 2. Count Upcoming (Next 7 Days)
        $upcomingCount = Appointment::where('doctor_id', $doctorId)
            ->where('appointment_date', '>', $today)
            ->where('appointment_date', '<=', $today->copy()->addDays(7))
            ->where('status', 'confirmed')
            ->count();

        return view('doctor.dashboard', compact('todaysAppointments', 'upcomingCount'));
    }

    /**
     * Show list of recent appointments for diagnosis/notes.
     */
    public function recentConsultations()
    {
        // Fetch appointments that are either Confirmed (Ready for checkup) or Completed (Done)
        $appointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', Auth::id())
            ->whereIn('status', ['confirmed', 'completed'])
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