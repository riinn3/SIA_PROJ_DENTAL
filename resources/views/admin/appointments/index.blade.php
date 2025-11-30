@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Appointment Manager</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <ul class="nav nav-pills card-header-pills">
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'pending' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'pending']) }}">Pending Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'confirmed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'confirmed']) }}">Confirmed Schedule</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'completed' ? 'active' : '' }}" href="{{ route('admin.appointments.index', ['status' => 'completed']) }}">History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status == 'cancelled' ? 'active' : 'text-danger' }}" href="{{ route('admin.appointments.index', ['status' => 'cancelled']) }}">Cancelled</a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Service</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $appt->appointment_time->format('h:i A') }}</small>
                            </td>
                            <td>{{ $appt->patient->name }}</td>
                            <td>Dr. {{ $appt->doctor->name }}</td>
                            <td>{{ $appt->service->name }}</td>
                            <td>
                                @if($status == 'pending')
                                    <form action="{{ route('admin.appointments.confirm', $appt->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Confirm</button>
                                    </form>
                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#cancelModal-{{ $appt->id }}">Reject</button>

                                @elseif($status == 'confirmed')
                                    <form action="{{ route('admin.appointments.complete', $appt->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button class="btn btn-info btn-sm">Done</button>
                                    </form>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#cancelModal-{{ $appt->id }}">Cancel</button>

                                @elseif($status == 'cancelled')
                                    <span class="text-danger small">Reason: {{ $appt->cancellation_reason }}</span>
                                @endif

                                <div class="modal fade" id="cancelModal-{{ $appt->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reason for Cancellation</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('admin.appointments.cancel', $appt->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <textarea name="cancellation_reason" class="form-control" placeholder="e.g. Patient No-Show, Doctor Emergency" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-danger">Confirm Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No {{ $status }} appointments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection