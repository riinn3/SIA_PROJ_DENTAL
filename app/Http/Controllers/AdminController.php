<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. KPI CARDS
        $pendingCount = Appointment::where('status', 'pending')->count();
        
        $todayAppointments = Appointment::whereDate('appointment_date', Carbon::today())
                            ->whereIn('status', ['confirmed', 'pending', 'completed'])
                            ->count();

        $totalPatients = User::where('role', 'patient')->count();

        // Total Earnings (Completed Appointments only)
        $earnings = Appointment::where('appointments.status', 'completed')
                    ->join('services', 'appointments.service_id', '=', 'services.id')
                    ->sum('services.price');

        // 2. CHART DATA: Monthly Revenue (Optimized)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $monthlyStats = Appointment::where('appointments.status', 'completed')
            ->where('appointment_date', '>=', $sixMonthsAgo)
            ->join('services', 'appointments.service_id', '=', 'services.id')
            // MySQL compatible date format
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month_key, SUM(services.price) as total")
            ->groupBy('month_key')
            ->pluck('total', 'month_key');

        $revenueData = [];
        $months = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $monthKey = $dt->format('Y-m');
            $months[] = $dt->format('M Y');
            $revenueData[] = $monthlyStats[$monthKey] ?? 0;
        }

        // 3. PIE CHART: Appointment Status (Optimized)
        $statusStats = Appointment::selectRaw('status, count(*) as count')
            ->whereIn('status', ['completed', 'confirmed', 'cancelled'])
            ->groupBy('status')
            ->pluck('count', 'status');

        $pieData = [
            $statusStats['completed'] ?? 0,
            $statusStats['confirmed'] ?? 0,
            $statusStats['cancelled'] ?? 0,
        ];

        // 4. "UP NEXT" for Admin (Earliest confirmed appointment slot, getting ALL simultaneous patients)
        $now = Carbon::now();
        
        $nextSlot = Appointment::where('status', 'confirmed')
            ->where(function($query) use ($now) {
                $query->whereDate('appointment_date', '>', $now->toDateString())
                      ->orWhere(function($q) use ($now) {
                          $q->whereDate('appointment_date', $now->toDateString())
                            ->whereTime('appointment_time', '>=', $now->toTimeString());
                      });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first(['appointment_date', 'appointment_time']);

        $nextPatients = collect([]);

        if ($nextSlot) {
            $nextPatients = Appointment::with(['patient', 'doctor', 'service'])
                ->where('status', 'confirmed')
                ->where('appointment_date', $nextSlot->appointment_date)
                ->where('appointment_time', $nextSlot->appointment_time)
                ->get();
        }

        return view('admin.dashboard', compact(
            'pendingCount', 'todayAppointments', 'totalPatients', 'earnings',
            'months', 'revenueData', 'pieData', 'nextPatients'
        ));
    }
}