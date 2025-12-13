<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $role = Auth::user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } 
        
        if ($role === 'doctor') {
            return redirect()->route('doctor.dashboard');
        }

        return redirect()->route('dashboard');
    }
}