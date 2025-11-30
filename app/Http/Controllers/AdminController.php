<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Pending requests
        $pendingCount = Appointment::where('status', 'pending')->count();

        // 2. Today's appointments
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
                            ->where('status', 'confirmed')
                            ->count();

        // 3. Total patients (FIXED: Uses 'role' instead of 'is_admin')
        $totalPatients = User::where('role', 'patient')->count();

        // 4. Total earnings
        $earnings = Appointment::where('appointments.status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

        return view('admin.dashboard', compact('pendingCount', 'todayAppointments', 'totalPatients', 'earnings'));
    }
}