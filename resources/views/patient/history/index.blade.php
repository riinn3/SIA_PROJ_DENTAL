@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Appointment History</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50 mr-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Transactions</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Treatment</th>
                            <th>Doctor</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            <td class="font-weight-bold text-dark">
                                {{ $appt->appointment_date->format('M d, Y') }}
                            </td>
                            <td>
                                {{ $appt->appointment_time->format('h:i A') }}
                                <span class="small text-muted">({{ $appt->duration_minutes }} mins)</span>
                            </td>
                            <td>{{ $appt->service->name }}</td>
                            <td>Dr. {{ $appt->doctor->name }}</td>
                            <td>₱{{ number_format($appt->service->price, 2) }}</td>
                            <td>
                                @if($appt->status == 'completed')
                                    <span class="badge badge-success px-2 py-1">Completed</span>
                                @elseif($appt->status == 'confirmed')
                                    <span class="badge badge-primary px-2 py-1">Confirmed</span>
                                @elseif($appt->status == 'cancelled')
                                    <span class="badge badge-danger px-2 py-1">Cancelled</span>
                                    <div class="small text-danger mt-1">
                                        Reason: {{ $appt->cancellation_reason ?? 'N/A' }}
                                    </div>
                                @else
                                    <span class="badge badge-warning px-2 py-1 text-dark">{{ ucfirst($appt->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3 text-gray-300"></i><br>
                                You haven't made any appointments yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-end">
                {{ $appointments->links() }}
            </div>
        </div>
    </div>

</div>
@endsection