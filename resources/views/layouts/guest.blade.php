<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Ponce Miranda') }}</title>
    
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ponce-skin.css') }}" rel="stylesheet">
    
    <style>
        .guest-nav { background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .nav-link { color: #5a5c69; font-weight: 600; }
        .nav-link:hover { color: #4e73df; }
        body { font-family: 'Nunito', sans-serif; }
    </style>
</head>
<body class="bg-light">

    {{-- 1. PUBLIC NAVBAR --}}
    <nav class="navbar navbar-expand-lg navbar-light guest-nav sticky-top">
        <div class="container">
            <a class="navbar-brand font-weight-bold text-primary" href="{{ route('home') }}">
                <i class="fas fa-tooth mr-2"></i> Ponce Miranda
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#guestMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="guestMenu">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('services.public.index') }}">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('doctors.public.index') }}">Doctors</a></li>
                </ul>
                <div class="form-inline">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm px-4 shadow-sm">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-dark font-weight-bold mr-3" style="text-decoration:none">Log In</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm px-4 shadow-sm">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- 2. HYBRID CONTENT AREA --}}
    {{-- This checks: Is there a $slot? If yes, echo it. If no, check for @section('content') --}}
    <main>
        {{ $slot ?? '' }} 
        @yield('content')
    </main>

    {{-- 3. FOOTER --}}
    <footer class="bg-white py-4 mt-5 border-top">
        <div class="container text-center text-muted small">
            &copy; {{ date('Y') }} Ponce Miranda Dental Clinic. All Rights Reserved.
        </div>
    </footer>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>