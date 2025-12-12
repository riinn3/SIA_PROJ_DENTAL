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
    /* Bigger, clearer slot buttons */
    .slot-btn { 
        font-weight: bold; 
        border-left: 6px solid transparent; 
        transition: all 0.2s; 
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .slot-btn:hover { border-left: 6px solid #1cc88a; background-color: #f0fff4; }
    .slot-btn.active { border-left: 6px solid #1cc88a; background-color: #1cc88a !important; color: white !important; }
    .slot-btn.active small { color: #e9f7ef !important; }
</style>

<script>
    var calendar;
    var selectedDoctorId = null;
    var globalDuration = 30; // Default to 30 mins

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
                if (info.dateStr < today) return;
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#eaecf4';
                fetchSlots(info.dateStr);
            }
        });
        calendar.render();
    });

    // STEP 1: Select Service
    function selectService(el, id, name, price, duration) {
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');
        
        // UPDATE GLOBAL DURATION
        globalDuration = parseInt(duration);
        
        document.getElementById('input_service_id').value = id;
        document.getElementById('sum_service').innerText = name;
        document.getElementById('sum_price').innerText = '₱' + price.toLocaleString();
        document.getElementById('sum_duration').innerText = duration + ' mins';

        unlockStep('step2');
    }

    // STEP 2: Select Doctor
    function selectDoctor(el, id, name) {
        document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');

        selectedDoctorId = id;
        document.getElementById('input_doctor_id').value = id;
        document.getElementById('sum_doctor').innerText = name;

        unlockStep('step3');
        calendar.refetchEvents();
    }

    // STEP 3: Fetch & Merge Slots (THE FIX)
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

                // --- SMART MERGING LOGIC ---
                // 1. Calculate how many 30-min slots we need
                let requiredSlots = Math.ceil(globalDuration / 30);
                let validOptionsFound = false;

                // 2. Loop through available slots
                // We stop loop early if there aren't enough slots left in the day
                for (let i = 0; i <= data.slots.length - requiredSlots; i++) {
                    
                    let isSequenceAvailable = true;
                    
                    // 3. Look ahead to check if the NEXT (requiredSlots - 1) slots are also free
                    for (let j = 0; j < requiredSlots; j++) {
                        if (data.slots[i + j].type !== 'available') {
                            isSequenceAvailable = false;
                            break; 
                        }
                    }

                    // 4. If sequence is valid, render ONE button for the whole block
                    if (isSequenceAvailable) {
                        validOptionsFound = true;
                        
                        let startSlot = data.slots[i];
                        let endSlot = data.slots[i + requiredSlots - 1]; // The last slot in the chain
                        
                        // Parse Labels (e.g., "1:30 PM - 2:00 PM")
                        let startTimeLabel = startSlot.time_label.split(' - ')[0]; // "1:30 PM"
                        let endTimeLabel = endSlot.time_label.split(' - ')[1];     // "3:30 PM" (from the last slot)
                        let fullLabel = `${startTimeLabel} - ${endTimeLabel}`;
                        
                        let btn = document.createElement('button');
                        btn.className = 'list-group-item list-group-item-action py-3 mb-2 shadow-sm rounded slot-btn';
                        btn.innerHTML = `
                            <div>
                                <div class="h6 mb-0">${fullLabel}</div>
                                <small class="text-muted">Duration: ${globalDuration} mins</small>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400"></i>
                        `;
                        btn.type = 'button';
                        // Pass the START time to the form
                        btn.onclick = function() { selectTime(this, date, startSlot.raw_time, fullLabel); };
                        container.appendChild(btn);
                    }
                }

                if (!validOptionsFound) {
                    container.innerHTML = `<div class="alert alert-secondary text-center small p-3">
                        No continuous ${globalDuration}-minute slot available.<br>Please try another date.
                    </div>`;
                }
            });
    }

    // STEP 4: Confirm
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