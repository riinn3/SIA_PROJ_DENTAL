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
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="pl-4">Date & Time</th>
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
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td class="pl-4">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-primary font-weight-bold">
                                    {{-- CALCULATE END TIME DYNAMICALLY --}}
                                    {{ $appt->appointment_time->format('h:i A') }} - 
                                    {{ $appt->appointment_time->copy()->addMinutes($appt->duration_minutes)->format('h:i A') }}
                                </div>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Guest' }}</div>
                                <div class="small text-muted">{{ $appt->patient->phone ?? 'No Phone' }}</div>
                            </td>
                            <td>
                                <div><i class="fas fa-user-md text-gray-400 mr-1"></i> Dr. {{ $appt->doctor->name }}</div>
                                <div class="small text-success font-weight-bold">{{ $appt->service->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-light border">
                                    {{-- DISPLAY DURATION NICELY --}}
                                    {{ $appt->duration_minutes >= 60 
                                        ? ($appt->duration_minutes / 60) . ' hr' . ($appt->duration_minutes > 60 ? 's' : '') 
                                        : $appt->duration_minutes . ' mins' }}
                                </span>
                            </td>

                            @if($status == 'cancelled')
                                <td class="text-danger small font-italic">
                                    "{{ $appt->cancellation_reason }}"<br>
                                    <span class="text-muted">By: {{ $appt->canceller->name ?? 'System' }}</span>
                                </td>
                            @else
                                <td class="text-right pr-4">
                                    <div class="btn-group" role="group">
                                        {{-- VIEW --}}
                                        <a href="{{ route('admin.appointments.show', $appt->id) }}" class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- PENDING ACTIONS --}}
                                        @if($status == 'pending')
                                            <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success ml-1" title="Confirm Booking">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger ml-1" data-toggle="modal" data-target="#cancelModal-{{ $appt->id }}" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>

                                        {{-- CONFIRMED ACTIONS --}}
                                        @elseif($status == 'confirmed')
                                            <form action="{{ route('admin.appointments.complete', $appt->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary ml-1" title="Mark as Completed" onclick="return confirm('Complete this appointment?')">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-warning ml-1" data-toggle="modal" data-target="#cancelModal-{{ $appt->id }}" title="Cancel">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="modal fade text-left" id="cancelModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Cancel Appointment</h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="{{ route('admin.appointments.cancel', $appt->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <p>Confirm cancellation for <strong>{{ $appt->patient->name }}</strong>?</p>
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                                                            <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">Confirm</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i><br>
                                No appointments found.
                            </td>
                        </tr>
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