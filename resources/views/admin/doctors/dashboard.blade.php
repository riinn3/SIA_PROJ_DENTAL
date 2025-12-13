@extends('layouts.doctor') {{-- Create a simplified layout for doctors --}}

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Doctor's Workspace</h1>

    <div class="row">
        {{-- TODAY'S AGENDA CARD --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Today's Appointments</h6>
                </div>
                <div class="card-body">
                    @if($todaysAppointments->isEmpty())
                        <p class="text-center text-muted my-5">No appointments scheduled for today.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Service</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaysAppointments as $appt)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $appt->appointment_time->format('h:i A') }}</td>
                                        <td>{{ $appt->patient->name }} <br> <small class="text-muted">{{ $appt->patient->phone }}</small></td>
                                        <td>{{ $appt->service->name }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#diagnosisModal-{{ $appt->id }}">
                                                <i class="fas fa-stethoscope"></i> Consult
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    {{-- DIAGNOSIS MODAL --}}
                                    <div class="modal fade" id="diagnosisModal-{{ $appt->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('doctor.diagnosis.update', $appt->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Consultation Notes: {{ $appt->patient->name }}</h5>
                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Medical Diagnosis</label>
                                                            <textarea name="diagnosis" class="form-control" rows="4" required>{{ $appt->diagnosis }}</textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">Prescription / Advice</label>
                                                            <textarea name="prescription" class="form-control" rows="3">{{ $appt->prescription }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Save & Complete</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- STATS CARD --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Overview</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <img class="img-fluid px-3 px-sm-4 mt-3 mb-4" style="width: 15rem;" src="img/undraw_medicine.svg" alt="...">
                    </div>
                    <p>You have <strong>{{ $upcomingCount }}</strong> upcoming appointments in the next 7 days.</p>
                    <a href="#" class="btn btn-primary btn-block">Manage Schedule</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection