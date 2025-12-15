<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class PatientHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Appointment::where('user_id', Auth::id())
            ->with(['doctor', 'service'])
            ->orderBy('appointment_date', 'desc');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('service', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(10)->withQueryString();
        
        return view('patient.history.index', compact('appointments', 'search'));
    }

    public function cancel($id)
    {
        $appt = Appointment::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        if ($appt->status != 'pending') {
            return back()->with('error', 'Only pending appointments can be cancelled.');
        }
        $appt->update([
            'status' => 'cancelled', 
            'cancellation_reason' => 'Patient requested cancellation',
            'cancelled_by' => Auth::id()
        ]);
        return back()->with('success', 'Appointment cancelled.');
    }
}