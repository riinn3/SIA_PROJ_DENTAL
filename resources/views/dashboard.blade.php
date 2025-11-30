@extends('layouts.admin') 
{{-- Using the Admin layout temporarily so it looks nice --}}

@section('content')
    <h1 class="h3 mb-4 text-gray-800">My Patient Portal</h1>

    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Welcome Back!</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ Auth::user()->name }}
                            </div>
                            <p class="mt-2">You can book appointments and view your history here.</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-injured fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection