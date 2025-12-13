@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Patient Consultations</h1>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Past Consultations</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Diagnosis Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appt)
                        <tr>
                            {{-- Date & Time --}}
                            <td class="align-middle">
                                <div class="font-weight-bold text-dark">{{ $appt->appointment_date->format('M d, Y') }}</div>
                                <div class="small text-muted">{{ $appt->appointment_time->format('h:i A') }}</div>
                            </td>

                            {{-- Patient Info --}}
                            <td class="align-middle">
                                <div class="font-weight-bold">{{ $appt->patient->name ?? 'Unknown/Archived Patient' }}</div>
                                <div class="small text-muted">{{ $appt->patient->phone ?? 'No Phone' }}</div>
                            </td>

                            {{-- Service --}}
                            <td class="align-middle">
                                <span class="badge badge-light border">{{ $appt->service->name }}</span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="align-middle">
                                @if($appt->diagnosis)
                                    <span class="badge badge-soft-success px-3 py-2 rounded-pill"><i class="fas fa-check mr-1"></i> Recorded</span>
                                @else
                                    <span class="badge badge-soft-warning px-3 py-2 rounded-pill"><i class="fas fa-pen mr-1"></i> Needs Notes</span>
                                @endif
                            </td>

                            {{-- Action Button & Modal --}}
                            <td class="align-middle text-right">
                                <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#diagnosisModal-{{ $appt->id }}">
                                    <i class="fas fa-file-medical-alt mr-1"></i> 
                                    {{ $appt->diagnosis ? 'Edit Notes' : 'Add Diagnosis' }}
                                </button>

                                {{-- DIAGNOSIS MODAL (Moved inside TD for valid HTML) --}}
                                <div class="modal fade text-left" id="diagnosisModal-{{ $appt->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content border-0 shadow-lg">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title font-weight-bold">Medical Notes: {{ $appt->patient->name ?? 'Unknown' }}</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('doctor.appointment.updateDiagnosis', $appt->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6 border-right">
                                                            <h6 class="font-weight-bold text-primary mb-3">Appointment Details</h6>
                                                            <p class="mb-1"><strong>Service:</strong> {{ $appt->service->name }}</p>
                                                            <p class="mb-1"><strong>Date:</strong> {{ $appt->appointment_date->format('F d, Y') }}</p>
                                                            <p class="mb-3"><strong>Time:</strong> {{ $appt->appointment_time->format('h:i A') }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="font-weight-bold text-dark">Clinical Diagnosis <span class="text-danger">*</span></label>
                                                                <textarea name="diagnosis" class="form-control" rows="4" placeholder="Enter findings here..." required>{{ $appt->diagnosis }}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold text-dark">Prescription / Advice</label>
                                                                <textarea name="prescription" class="form-control" rows="3" placeholder="Meds or instructions...">{{ $appt->prescription }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                        <i class="fas fa-save mr-1"></i> Save Record
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i><br>
                                No recent consultations found.
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
</div>
@endsection