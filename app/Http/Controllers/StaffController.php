<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteStaff;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $view = $request->get('view', 'active');

        if ($view === 'archived') {
            // UPDATED: paginate(10)
            $staff = User::onlyTrashed()->whereIn('role', ['admin', 'doctor'])->paginate(10);
        } else {
            // UPDATED: paginate(10)
            $staff = User::whereIn('role', ['admin', 'doctor'])->orderBy('created_at', 'desc')->paginate(10);
        }
                     
        return view('admin.staff.index', compact('staff', 'view'));
    }

    // 2. SHOW INVITE FORM
    public function create()
    {
        return view('admin.staff.create');
    }

    // 3. STORE (Simple Create for now)
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,doctor',
            'phone' => 'nullable|string'
        ]);

        // Create user with a dummy password (they can reset it later)
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'password' => Hash::make('password'), // Default password
            'email_verified_at' => now() 
        ]);

        return redirect()->route('admin.staff.index')
            ->with('success', "New {$request->role} added successfully.");
    }

    // ARCHIVE (Soft Delete)
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) return back()->with('error', 'Cannot delete self.');
        $user->delete();
        return back()->with('success', 'Staff member archived.');
    }

    // RESTORE
    public function restore($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Staff member restored.');
    }
}