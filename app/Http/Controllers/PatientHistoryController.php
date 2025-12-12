<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientHistoryController extends Controller
{
    /**
     * Show full appointment history
     */
    public function index()
    {
        $appointments = Appointment::where('user_id', Auth::id())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(10); // Pagination is key for long histories

        return view('patient.history.index', compact('appointments'));
    }
}