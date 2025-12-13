@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointment Management</h1>
        <a href="{{ route('admin.schedules.index') }}" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-calendar-plus fa-sm text-white-50"></i> Book via Calendar
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">
                        <i class="fas fa-clock mr-1"></i> Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'confirmed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">
                        <i class="fas fa-check mr-1"></i> Confirmed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'completed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">
                        <i class="fas fa-history mr-1"></i> History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'cancelled' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}">
                        <i class="fas fa-ban mr-1"></i> Cancelled
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body bg-light border-bottom">
            <form action="{{ route('admin.appointments.index') }}" method="GET" class="form-inline">
                <input type="hidden" name="status" value="{{ $status }}">
                
                <div class="input-group mr-2 mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-white"><i class="fas fa-search text-gray-400"></i></div>
                    </div>
                    <input type="text" class="form-control" name="search" placeholder="Patient or Doctor..." value="{{ $search }}">
                </div>

                <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">From:</label>
                <input type="date" name="start_date" class="form-control mr-2 mb-2" value="{{ $startDate }}">

                <label class="mr-2 mb-2 text-gray-600 font-weight-bold small">To:</label>
                <input type="date" name="end_date" class="form-control mr-2 mb-2" value="{{ $endDate }}">

                <button type="submit" class="btn btn-primary mb-2 shadow-sm">Filter</button>
                <a href="{{ route('admin.appointments.index', ['status' => $status]) }}" class="btn btn-secondary mb-2 ml-2 shadow-sm">Reset</a>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" width="100%" cellspacing="0">
                    
                    {{-- SORTING LOGIC START --}}
                    @php
                        // Helper to calculate the opposite direction for the next click
                        $nextDir = $direction == 'asc' ? 'desc' : 'asc';
                        // Icon to show current state
                        $sortIcon = $direction == 'asc' ? 'fa-sort-up' : 'fa-sort-down';
                    @endphp

                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="pl-4">
                                {{-- CLICKABLE HEADER --}}
                                <a href="{{ route('admin.appointments.index', array_merge(request()->all(), ['sort' => 'appointment_date', 'direction' => $nextDir])) }}" class="text-gray-700 text-decoration-none font-weight-bold">
                                    Date & Time 
                                    @if($sort == 'appointment_date') 
                                        <i class="fas {{ $sortIcon }} ml-1"></i> 
                                    @else
                                        <i class="fas fa-sort text-gray-400 ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Patient</th>
                            <th>Doctor / Service</th>
                            <th>Duration</th>
                            @if($status == 'cancelled')
                                <th>Reason</th>
                            @else
                                <th class="text-right pr-4">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    {{-- SORTING LOGIC END --}}

                    <tbody>
                        @forelse($appointments as $appt)
                        {{-- ... (Keep your existing table body rows here) ... --}}
                        <tr>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-primary font-weight-bold">
                                    {{ $appt->appointment_time->format('h:i A') }}
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Unknown' }}</div>
                                <div class="small text-muted">{{ $appt->patient->phone ?? 'No # ' }}</div>
                            </td>
                            <td>
                                <div><i class="fas fa-user-md text-gray-400 mr-1"></i> Dr. {{ $appt->doctor->name ?? 'Unavailable' }}</div>
                                <div class="small text-success font-weight-bold">{{ $appt->service->name ?? 'Custom' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ $appt->duration_minutes }} mins</span>
                            </td>
                            {{-- ... rest of your row logic ... --}}
                            @if($status == 'cancelled')
                                <td class="text-danger small font-italic">"{{ $appt->cancellation_reason }}"</td>
                            @else
                                <td class="text-right pr-4">
                                    {{-- Use your existing action buttons here --}}
                                    <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    {{-- ... etc ... --}}
                                </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">No appointments found.</td></tr>
                        @endforelse
                    </tbody>    
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white d-flex justify-content-end">
            {{ $appointments->links() }}
        </div>
    </div>

@endsection