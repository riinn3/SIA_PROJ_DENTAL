<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    // In app/Http/Controllers/PatientController.php
    public function index(Request $request)
    {
        $view = $request->get('view', 'all'); // Default to 'all' or 'active'? Let's default to 'all' as requested "add all patient" might imply it's important. Or stick to 'active'. Let's default to 'active' to match UI, but maybe user wants 'all' as default. Let's keep 'active' default for now but support 'all'.
        // Actually, let's make 'all' the default if that's what usually expected. But I'll stick to 'active' default to avoid changing startup behavior unless asked.
        $view = $request->get('view', 'active');
        $search = $request->get('search');

        // Base Query: Start with patients only
        $query = User::where('role', 'patient');

        // Apply Search (works across all tabs)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($view === 'all') {
            // ALL: Every non-archived patient
            $patients = $query->withCount('appointments')
                              ->orderBy('name')
                              ->paginate(10);

        } elseif ($view === 'pending') {
            // PENDING: Has an email, but hasn't verified it yet.
            $patients = $query->whereNull('email_verified_at')
                            ->whereNotNull('email') 
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'walkin') {
            // WALK-IN: Patients with NO email address
            $patients = $query->whereNull('email')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        } elseif ($view === 'archived') {
            // ARCHIVED: Soft deleted users
            $patients = User::onlyTrashed()
                            ->where('role', 'patient')
                            ->paginate(10);

        } else { // 'active'
            // ONLINE / ACTIVE: Verified Email Users
            $patients = $query->whereNotNull('email_verified_at')
                            ->withCount('appointments')
                            ->orderBy('name')
                            ->paginate(10);
        }

        return view('admin.patients.index', compact('patients', 'search', 'view'));
    }

    // 2. SHOW & EDIT (These make your buttons work)
    public function show(Request $request, $id)
    {
        // 1. Find the patient
        $patient = User::findOrFail($id);
        
        // 2. Filter Parameters for Appointments Tab
        $status = $request->get('status', 'all'); // 'all', 'incoming', 'completed', 'cancelled'
        $search = $request->get('search');

        // 3. Build Appointments Query
        $query = $patient->appointments()->with(['doctor', 'service'])->orderBy('appointment_date', 'desc');

        if ($status !== 'all') {
            if ($status === 'incoming') {
                $query->whereIn('status', ['pending', 'confirmed']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('doctor', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('service', function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(5);
        
        // 4. Calculate current status for the tab highlight (can differ from filter)
        // Actually, let's use the filter status for the tab.
        $currentStatus = $status;

        return view('admin.patients.show', compact('patient', 'appointments', 'search', 'currentStatus'));
    }

    public function edit($id)
    {
        $patient = User::where('role', 'patient')->findOrFail($id);
        return view('admin.patients.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $patient = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($patient->id)],
            'phone' => ['nullable', 'regex:/^09\d{9}$/'], 
        ]);

        $patient->fill($request->all());

        if ($patient->isDirty('email')) {
            $patient->email_verified_at = null;
            
            if ($patient->email) {
                $patient->sendEmailVerificationNotification();
            }
        }

        $patient->save();

        return redirect()->route('admin.patients.show', $id)->with('success', 'Patient details updated successfully.');
    }

    // 3. ARCHIVE ACTIONS
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Patient archived.');
    }

    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Patient restored.');
    }

    public function create()
    {
        return view('admin.patients.create');
    }

    // Find the store() method and replace it
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['nullable', 'regex:/^09\d{9}$/'], // Updated phone validation
            'password' => 'required|string|min:8',
        ]);

        // 1. Create User with the provided password
        // Do NOT auto-verify. Let them verify via email.
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password), 
            'role' => 'patient',
        ]);

        // 2. Trigger Email Verification
        event(new \Illuminate\Auth\Events\Registered($user));

        return redirect()->route('admin.patients.index')
            ->with('success', 'Patient registered. A verification email has been sent to them.');
    }
}