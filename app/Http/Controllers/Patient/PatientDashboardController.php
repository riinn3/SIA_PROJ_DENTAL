<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Standard: Separate Upcoming vs History
        $upcoming = Appointment::where('user_id', $user->id)
            ->where('appointment_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('appointment_date')
            ->get();

        $history = Appointment::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('appointment_date', '<', now())
                  ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->orderByDesc('appointment_date')
            ->limit(5)
            ->get();

        return view('patient.dashboard', compact('upcoming', 'history'));
    }
}