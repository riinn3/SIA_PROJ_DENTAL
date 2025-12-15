<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Defaults (This Month)
        $start = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $end = $request->get('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

        // 2. MAIN LEDGER (Detailed Transactions)
        $completedAppts = Appointment::with(['service', 'doctor', 'patient' => function($query) { $query->withTrashed(); }])
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->orderBy('appointment_date')
            ->get();

        // 3. SERVICE PERFORMANCE (Which treatment sells best?)
        $serviceStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('services.name')
            ->orderByDesc('revenue')
            ->get();

        // 4. DOCTOR PRODUCTIVITY (Who is working the most?)
        $doctorStats = Appointment::query()
            ->where('appointments.status', 'completed')
            ->whereBetween('appointment_date', [$start, $end])
            ->join('users', 'appointments.doctor_id', '=', 'users.id')
            ->join('services', 'appointments.service_id', '=', 'services.id')
            ->select('users.name', DB::raw('count(*) as count'), DB::raw('sum(appointments.price) as revenue'))
            ->groupBy('users.name')
            ->orderByDesc('count')
            ->get();

        // 5. CANCELLATION AUDIT
        $cancelledAppts = Appointment::with(['patient' => function($query) { $query->withTrashed(); }, 'canceller'])
            ->where('appointments.status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->get();

        // 6. TOTALS
        $stats = [
            'total_completed' => $completedAppts->count(),
            'total_cancelled' => $cancelledAppts->count(),
            'revenue' => $completedAppts->sum('price')
        ];

        return view('admin.reports.index', compact(
            'completedAppts', 'cancelledAppts', 'serviceStats', 'doctorStats', 'stats', 'start', 'end'
        ));
    }
}
