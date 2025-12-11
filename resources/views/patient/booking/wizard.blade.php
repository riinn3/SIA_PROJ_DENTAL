@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Book Appointment</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm shadow-sm">Cancel</a>
    </div>

    <form action="{{ route('patient.booking.store') }}" method="POST" id="bookingForm">
        @csrf
        <input type="hidden" name="service_id" id="input_service_id" required>
        <input type="hidden" name="doctor_id" id="input_doctor_id" required>
        <input type="hidden" name="appointment_date" id="input_date" required>
        <input type="hidden" name="appointment_time" id="input_time" required>

        <div class="row">
            <div class="col-lg-8">

                <div class="card shadow mb-4 border-left-primary" id="step1">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">1. Select Service</h6></div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($services as $service)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 service-card cursor-pointer" 
                                     onclick="selectService(this, {{ $service->id }}, '{{ $service->name }}', {{ $service->price }}, {{ $service->duration_minutes }})">
                                    <div class="card-body">
                                        <div class="font-weight-bold text-dark">{{ $service->name }}</div>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span class="badge badge-success">₱{{ number_format($service->price) }}</span>
                                            <span class="badge badge-light border">{{ $service->duration_minutes }} mins</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 border-left-success disabled-section" id="step2" style="opacity:0.5; pointer-events:none;">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-success">2. Select Doctor</h6></div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($doctors as $doc)
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 doctor-card cursor-pointer text-center py-3"
                                     onclick="selectDoctor(this, {{ $doc->id }}, 'Dr. {{ $doc->name }}')">
                                    <div class="card-body p-2">
                                        <i class="fas fa-user-md fa-3x text-gray-300 mb-2"></i>
                                        <h6 class="font-weight-bold text-dark mb-0">Dr. {{ $doc->name }}</h6>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 border-left-info disabled-section" id="step3" style="opacity:0.5; pointer-events:none;">
                    <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-info">3. Select Date & Time</h6></div>
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <div class="col-md-7 p-3 border-right">
                                <div id="calendar"></div>
                            </div>
                            <div class="col-md-5 p-3 bg-light">
                                <h6 class="text-center font-weight-bold mb-3" id="dateLabel">Select a Date</h6>
                                <div id="slotsContainer" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center text-muted small mt-5">Available times will appear here.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header py-3 bg-dark text-white"><h6 class="m-0 font-weight-bold">Booking Summary</h6></div>
                    <div class="card-body">
                        <div class="mb-3 border-bottom pb-2">
                            <small class="text-muted font-weight-bold">TREATMENT</small>
                            <div class="h5 font-weight-bold text-dark" id="sum_service">--</div>
                            <div class="d-flex justify-content-between">
                                <span class="text-success font-weight-bold" id="sum_price">--</span>
                                <span class="small" id="sum_duration">--</span>
                            </div>
                        </div>
                        <div class="mb-3 border-bottom pb-2">
                            <small class="text-muted font-weight-bold">DOCTOR</small>
                            <div class="h6 font-weight-bold text-dark" id="sum_doctor">--</div>
                        </div>
                        <div class="mb-4">
                            <small class="text-muted font-weight-bold">DATE & TIME</small>
                            <div class="h5 font-weight-bold text-primary" id="sum_date">--</div>
                            <div class="h6 text-dark" id="sum_time">--</div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block btn-lg" id="btnConfirm" disabled>Confirm Booking</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<style>
    .cursor-pointer { cursor: pointer; }
    .selected-card { border: 2px solid #4e73df !important; background-color: #f8f9fc; transform: scale(1.02); }
    .slot-btn { font-weight: bold; border-left: 4px solid transparent; transition: all 0.2s; }
    .slot-btn:hover { border-left: 4px solid #1cc88a; background-color: #f0fff4; }
    .slot-btn.active { border-left: 4px solid #1cc88a; background-color: #1cc88a !important; color: white !important; }
</style>

<script>
    var calendar;
    var selectedDoctorId = null;
    var globalDuration = 30; // Default

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var today = new Date().toISOString().split('T')[0];

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 400,
            headerToolbar: { left: 'prev,next', center: 'title', right: '' },
            selectable: true,
            validRange: { start: today },
            events: function(info, successCallback, failureCallback) {
                if (!selectedDoctorId) { successCallback([]); return; }
                fetch(`{{ route('api.calendar') }}?doctor_id=${selectedDoctorId}&start=${info.startStr}&end=${info.endStr}`)
                    .then(r => r.json()).then(data => successCallback(data));
            },
            dateClick: function(info) {
                if (!selectedDoctorId) return;
                if (info.dateStr < today) return; // Prevent past dates
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#eaecf4';
                fetchSlots(info.dateStr);
            }
        });
        calendar.render();
    });

    // STEP 1
    function selectService(el, id, name, price, duration) {
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');
        
        // UPDATE GLOBAL DURATION
        globalDuration = parseInt(duration);
        console.log("Service Selected. Duration set to:", globalDuration); // DEBUG

        document.getElementById('input_service_id').value = id;
        document.getElementById('sum_service').innerText = name;
        document.getElementById('sum_price').innerText = '₱' + price.toLocaleString();
        document.getElementById('sum_duration').innerText = duration + ' mins';

        unlockStep('step2');
    }

    // STEP 2
    function selectDoctor(el, id, name) {
        document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');

        selectedDoctorId = id;
        document.getElementById('input_doctor_id').value = id;
        document.getElementById('sum_doctor').innerText = name;

        unlockStep('step3');
        calendar.refetchEvents();
    }

    // STEP 3: THE SMART LOGIC
    function fetchSlots(date) {
        const container = document.getElementById('slotsContainer');
        document.getElementById('dateLabel').innerText = new Date(date).toDateString();
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

        fetch(`{{ route('api.day_details') }}?date=${date}&doctor_id=${selectedDoctorId}`)
            .then(r => r.json())
            .then(data => {
                container.innerHTML = '';
                
                if (data.status === 'closed') {
                    container.innerHTML = '<div class="alert alert-warning small m-2">Doctor not working.</div>';
                    return;
                }

                // CALCULATE CHUNKS
                let chunks = Math.ceil(globalDuration / 30);
                console.log(`Need ${chunks} slots for ${globalDuration} mins`);

                let validFound = false;

                // Loop through slots
                for (let i = 0; i <= data.slots.length - chunks; i++) {
                    let isSequenceOpen = true;
                    let endLabel = "";

                    // Look ahead
                    for (let j = 0; j < chunks; j++) {
                        let slot = data.slots[i + j];
                        
                        // IF SLOT IS NOT 'available' (It is 'booked' or 'lunch'), FAIL.
                        if (slot.type !== 'available') {
                            isSequenceOpen = false;
                            break; 
                        }
                        if (j === chunks - 1) endLabel = slot.time_label.split(' - ')[1];
                    }

                    if (isSequenceOpen) {
                        validFound = true;
                        let startLabel = data.slots[i].time_label.split(' - ')[0];
                        let fullLabel = `${startLabel} - ${endLabel}`;
                        let rawStartTime = data.slots[i].raw_time;

                        let btn = document.createElement('button');
                        btn.className = 'list-group-item list-group-item-action text-center py-3 mb-2 shadow-sm rounded slot-btn';
                        btn.innerHTML = `
                            <div class="h6 mb-0 text-dark">${fullLabel}</div>
                            <small class="text-success font-weight-bold">Available</small>
                        `;
                        btn.type = 'button';
                        btn.onclick = function() { selectTime(this, date, rawStartTime, fullLabel); };
                        container.appendChild(btn);
                    }
                }

                if (!validFound) {
                    container.innerHTML = `<div class="alert alert-secondary text-center small p-3">No space for a ${globalDuration}-min appointment.<br>Try another day.</div>`;
                }
            });
    }

    // STEP 4
    function selectTime(el, date, rawTime, label) {
        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active'));
        el.classList.add('active');

        document.getElementById('input_date').value = date;
        document.getElementById('input_time').value = rawTime;
        document.getElementById('sum_date').innerText = new Date(date).toDateString();
        document.getElementById('sum_time').innerText = label;

        document.getElementById('btnConfirm').disabled = false;
    }

    function unlockStep(id) {
        const step = document.getElementById(id);
        step.style.opacity = '1';
        step.style.pointerEvents = 'auto';
        step.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
@endpush