<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

/**
 * Manages the main dashboard for the patient.
 */
class PatientHomeController extends Controller
{
    /**
     * Display the patient dashboard.
     * 
     * Retrieves two key pieces of information:
     * 1. The immediate next upcoming appointment (to display as a hero card).
     * 2. A brief history of recent past or completed appointments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Retrieve the single earliest upcoming appointment.
        // We include 'pending' status so the user knows their request is in the system.
        $upcoming = Appointment::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'pending']) 
            ->where('appointment_date', '>=', Carbon::today())
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->with(['doctor', 'service'])
            ->first();

        // Retrieve a limited history of past, completed, or cancelled appointments.
        $history = Appointment::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('appointment_date', '<', Carbon::today())
                  ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->orderBy('appointment_date', 'desc')
            ->take(5)
            ->with(['doctor', 'service'])
            ->get();

        return view('patient.dashboard', compact('upcoming', 'history'));
    }
}