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
    public function recentConsultations(Request $request)
    {
        $doctorId = Auth::id();
        $search = $request->query('search');

        $appointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->where('status', 'completed');

        if ($search) {
            $appointments->where(function ($query) use ($search) {
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }
            
        $appointments = $appointments->orderByDesc('appointment_date') // Newest first
            ->orderByDesc('appointment_time')
            ->paginate(10);

        return view('doctor.consultations', compact('appointments', 'search'));
    }

    /**
     * Show a list of patients for consultation history.
     */
    public function patientList(Request $request)
    {
        $doctorId = Auth::id();
        $search = $request->query('search');

        // Get unique patient IDs who had completed appointments with this doctor
        $patientIds = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'completed')
            ->distinct('user_id')
            ->pluck('user_id');

        // Fetch the patients
        $patients = \App\Models\User::whereIn('id', $patientIds)
            ->where('role', 'patient'); // Ensure they are patients

        if ($search) {
            $patients->where('name', 'like', '%' . $search . '%');
        }

        $patients = $patients->orderBy('name')
            ->paginate(10);

        return view('doctor.consultations', compact('patients', 'search'));
    }

    /**
     * Show detailed consultation history for a specific patient.
     * Includes appointments from all doctors.
     */
    public function showPatientConsultations(\App\Models\User $patient, Request $request)
    {
        // Ensure the fetched user is indeed a patient
        if ($patient->role !== 'patient') {
            abort(404); // Or redirect with an error
        }

        $search = $request->query('search');

        $appointments = Appointment::with(['doctor', 'service'])
            ->where('user_id', $patient->id)
            ->where('status', 'completed');
            
        // Search by service name if a search term is present
        if ($search) {
            $appointments->whereHas('service', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        $appointments = $appointments->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10);

        return view('doctor.patient_consultations_detail', compact('patient', 'appointments', 'search'));
    }

    /**
     * Show list of completed consultations for the current day for the logged-in doctor.
     */
    public function todaysConsultations(Request $request)
    {
        $doctorId = Auth::id();
        $today = Carbon::today();
        $search = $request->query('search');

        $appointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('status', 'completed'); // Only completed appointments for today

        if ($search) {
            $appointments->where(function ($query) use ($search) {
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('service', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }
            
        $appointments = $appointments->orderByDesc('appointment_time') // Order by time for today
            ->paginate(10);

        return view('doctor.todays_consultations', compact('appointments', 'search'));
    }


    /**
     * Save Medical Notes / Diagnosis.
     */
    public function updateDiagnosis(Request $request, Appointment $appointment) // Using Route Model Binding
    {
        $request->validate([
            'diagnosis' => 'required|string|min:5',
            'prescription' => 'nullable|string'
        ]);

        // Ensure the logged-in doctor is authorized to update this appointment
        if ($appointment->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->update([
            'diagnosis' => $request->diagnosis,
            'prescription' => $request->prescription,
            'status' => 'completed' // Auto-complete the appointment when notes are added
        ]);

        return back()->with('success', 'Consultation notes saved successfully.');
    }
}