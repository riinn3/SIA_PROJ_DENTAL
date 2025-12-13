<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;

class PublicController extends Controller
{
    /**
     * Show the public homepage.
     */
    public function index()
    {
        // Load key data like featured services or total counts for display
        $services = Service::limit(3)->get();
        // Count doctors (users with role 'doctor')
        $doctorCount = User::where('role', 'doctor')->count();

        return view('public.home', compact('services', 'doctorCount'));
    }

    /**
     * Show a public list of all services.
     */
    public function services()
    {
        $services = Service::orderBy('name')->get();
        return view('public.services', compact('services'));
    }

    /**
     * Show a public list of all available doctors.
     */
    public function doctors(Request $request)
    {
        $search = $request->get('search');

        $query = User::where('role', 'doctor');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $doctors = $query->orderBy('name')->paginate(12);

        return view('public.doctors', compact('doctors', 'search'));
    }

    /**
     * Show a single doctor's profile.
     */
    public function doctorProfile($id)
    {
        $doctor = User::where('role', 'doctor')->findOrFail($id);
        $services = Service::orderBy('name')->get(); 

        return view('public.doctor-profile', compact('doctor', 'services'));
    }
}