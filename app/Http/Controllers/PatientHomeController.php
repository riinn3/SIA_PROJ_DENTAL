<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use Carbon\Carbon;

class PatientHomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Get the NEXT upcoming appointment (Confirmed only)
        $upcoming = Appointment::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'pending']) // Show pending too so they know it's processed
            ->where('appointment_date', '>=', Carbon::today())
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->with(['doctor', 'service'])
            ->first();

        // 2. Get Past History (Completed or Cancelled)
        $history = Appointment::where('user_id', $user->id)
            ->where(function($q) {
                $q->where('appointment_date', '<', Carbon::today())
                  ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->orderBy('appointment_date', 'desc')
            ->take(5) // Just the last 5
            ->with(['doctor', 'service'])
            ->get();

        return view('patient.dashboard', compact('upcoming', 'history'));
    }
}