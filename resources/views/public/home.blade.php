@extends('layouts.guest')

@section('content')
<div class="container-fluid p-0">
    
    {{-- 1. HERO SECTION --}}
    {{-- Added 'mb-5' for spacing below the pink curve --}}
    <div class="hero-curve text-white text-center position-relative mb-5" style="padding-top: 4rem; padding-bottom: 5rem;">
        <div class="container">
            <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem;">Ponce Miranda Dental</h1>
            <p class="lead mb-4 opacity-90" style="font-size: 1.1rem;">Experience elegant care for your perfect smile.</p>
            
            <div class="d-flex justify-content-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light text-primary font-weight-bold shadow-sm px-4 rounded-pill">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light text-primary font-weight-bold shadow-sm px-4 mr-2 rounded-pill">
                        Log In
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light font-weight-bold px-4 rounded-pill">
                        New Patient
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- 2. CARDS SECTION --}}
    {{-- Removed negative margin. Added 'py-5' to create proper whitespace --}}
    <div class="container py-4">
        <div class="row text-center justify-content-center">
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-primary bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-primary text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-user-md fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Expert Team</h5>
                        <p class="text-muted small mb-3">Qualified professionals ready to help.</p>
                        <a href="{{ route('doctors.public.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-4">See Doctors</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-success bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-success text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-tooth fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Premium Care</h5>
                        <p class="text-muted small mb-3">Treatments tailored to your needs.</p>
                        <a href="{{ route('services.public.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-4">View Services</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <div class="card h-100 border-left-info bg-white shadow-sm hover-scale">
                    <div class="card-body p-4">
                        <div class="icon-circle bg-info text-white mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" 
                             style="width: 60px; height: 60px;">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                        <h5 class="font-weight-bold text-dark">Easy Booking</h5>
                        <p class="text-muted small mb-3">Schedule your visit in clicks.</p>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-outline-info rounded-pill px-4">Book Now</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Small hover effect for cards */
    .hover-scale { transition: transform 0.2s; }
    .hover-scale:hover { transform: translateY(-3px); }
</style>
@endsection