@extends('layouts.admin')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Book Walk-In Appointment</h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.appointments.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Patient</label>
                        <select name="user_id" class="form-control">
                            @foreach($patients as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->email }})</option>
                            @endforeach
                        </select>
                        <small><a href="#">+ Register New Patient</a></small>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Doctor</label>
                        <select name="doctor_id" class="form-control">
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">Dr. {{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" class="form-control">
                        @foreach($services as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} - ₱{{ $s->price }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Date</label>
                        <input type="date" name="appointment_date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Time</label>
                        <input type="time" name="appointment_time" class="form-control" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Confirm Booking</button>
            </form>
        </div>
    </div>
@endsection