@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Book Appointment</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-secondary shadow-sm">Cancel</a>
    </div>

    <form action="{{ route('patient.booking.store') }}" method="POST" id="bookingForm">
        @csrf
        <input type="hidden" name="service_id" id="input_service_id" required>
        <input type="hidden" name="doctor_id" id="input_doctor_id" required>
        <input type="hidden" name="appointment_date" id="input_date" required>
        <input type="hidden" name="appointment_time" id="input_time" required>

        <div class="row">
            <div class="col-lg-8">

                <div class="card shadow mb-4" id="step1">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">1. Select Service</h6>
                    </div>
                    <div class="card-body">
                        
                        <h6 class="text-primary font-weight-bold mb-3">Popular Treatments</h6>
                        <div class="row mb-3">
                            @foreach($services->take(2) as $service)
                            <div class="col-md-6 mb-2">
                                <div class="card border-left-primary h-100 service-card cursor-pointer shadow-sm"
                                     onclick="selectService(this, {{ $service->id }}, '{{ $service->name }}', {{ $service->price }}, {{ $service->duration_minutes }})">
                                    <div class="card-body py-3">
                                        <div class="font-weight-bold text-dark">{{ $service->name }}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted"><i class="fas fa-clock"></i> {{ $service->duration_minutes }}m</small>
                                            <span class="font-weight-bold text-success">₱{{ number_format($service->price) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($services->count() > 2)
                        <h6 class="text-secondary font-weight-bold mb-2 mt-4">Other Services</h6>
                        <div class="input-group">
                            <select class="custom-select" id="otherServiceSelect" onchange="selectFromDropdown(this)">
                                <option value="" selected>-- Select a treatment --</option>
                                @foreach($services->skip(2) as $service)
                                    <option value="{{ $service->id }}" 
                                            data-name="{{ $service->name }}" 
                                            data-price="{{ $service->price }}" 
                                            data-duration="{{ $service->duration_minutes }}">
                                        {{ $service->name }} ({{ $service->duration_minutes }} mins) - ₱{{ number_format($service->price) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                    </div>
                </div>

                <div class="card shadow mb-4 disabled-section" id="step2" style="opacity: 0.5; pointer-events: none;">
                    <div class="card-header py-3 bg-secondary text-white" id="header2">
                        <h6 class="m-0 font-weight-bold">2. Select Doctor</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($doctors as $doc)
                            <div class="col-md-4 mb-2">
                                <div class="card shadow-sm h-100 doctor-card cursor-pointer text-center py-3"
                                     onclick="selectDoctor(this, {{ $doc->id }}, 'Dr. {{ $doc->name }}')">
                                    <div class="card-body p-2">
                                        <i class="fas fa-user-md fa-2x text-gray-400 mb-2"></i>
                                        <h6 class="font-weight-bold text-dark mb-0">Dr. {{ $doc->name }}</h6>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4 disabled-section" id="step3" style="opacity: 0.5; pointer-events: none;">
                    <div class="card-header py-3 bg-secondary text-white" id="header3">
                        <h6 class="m-0 font-weight-bold">3. Select Date & Time</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="row no-gutters">
                            <div class="col-md-7 border-right p-3">
                                <div id="calendar"></div>
                            </div>
                            <div class="col-md-5 p-3 bg-light">
                                <h6 class="font-weight-bold text-center mb-3" id="selectedDateLabel">Select a Date</h6>
                                <div id="slotsContainer" class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                    <div class="text-center text-muted mt-5 small">
                                        Select a green date to view slots.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4">
                <div class="card shadow mb-4 sticky-top" style="top: 20px;">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Booking Summary</h6>
                    </div>
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
                            <small class="text-muted font-weight-bold">DATE</small>
                            <div class="h5 font-weight-bold text-primary" id="sum_date">--</div>
                            <div class="h6 text-dark" id="sum_time">--</div>
                        </div>
                        <button type="submit" class="btn btn-success btn-block btn-lg" id="btnConfirm" disabled>
                            Confirm Booking
                        </button>
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
    /* Disable past dates visual */
    .fc-day-past { background-color: #f8f9fc; pointer-events: none; opacity: 0.6; }
</style>

<script>
    var calendar;
    var selectedServiceId = null;
    var selectedDoctorId = null;

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var today = new Date().toISOString().split('T')[0]; // Get YYYY-MM-DD

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 400,
            headerToolbar: { left: 'prev,next', center: 'title', right: '' },
            selectable: true,
            // BLOCK PAST DATES
            validRange: {
                start: today
            },
            events: function(info, successCallback, failureCallback) {
                // Now points to the SHARED public API route
                if (!selectedDoctorId) { successCallback([]); return; }
                fetch(`{{ route('api.calendar') }}?doctor_id=${selectedDoctorId}&start=${info.startStr}&end=${info.endStr}`)
                    .then(r => r.json()).then(data => successCallback(data));
            },
            dateClick: function(info) {
                if (!selectedDoctorId) return;
                
                // Extra check for past dates
                if (info.dateStr < today) return; 

                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#eaecf4';
                
                fetchSlots(info.dateStr);
            }
        });
        calendar.render();
    });

    // STEP 1: CARD SELECT
    function selectService(el, id, name, price, duration) {
        // Reset Dropdown
        document.getElementById('otherServiceSelect').selectedIndex = 0;
        applyServiceSelection(id, name, price, duration);
        
        // Highlight Card
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');
    }

    // STEP 1: DROPDOWN SELECT
    function selectFromDropdown(select) {
        if(select.value == "") return;

        // Reset Cards
        document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected-card'));

        let opt = select.options[select.selectedIndex];
        applyServiceSelection(
            select.value, 
            opt.getAttribute('data-name'), 
            parseFloat(opt.getAttribute('data-price')), 
            opt.getAttribute('data-duration')
        );
    }

    // COMMON LOGIC
    function applyServiceSelection(id, name, price, duration) {
        selectedServiceId = id;
        document.getElementById('input_service_id').value = id;
        document.getElementById('sum_service').innerText = name;
        document.getElementById('sum_price').innerText = '₱' + price.toLocaleString();
        document.getElementById('sum_duration').innerText = duration + ' mins';

        unlockSection('step2', 'header2', 'bg-secondary', 'bg-primary');
        document.getElementById('step2').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // STEP 2: DOCTOR
    function selectDoctor(el, id, name) {
        document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected-card'));
        el.classList.add('selected-card');

        selectedDoctorId = id;
        document.getElementById('input_doctor_id').value = id;
        document.getElementById('sum_doctor').innerText = name;

        unlockSection('step3', 'header3', 'bg-secondary', 'bg-primary');
        calendar.refetchEvents();
        document.getElementById('step3').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // STEP 3: API CALL
    function fetchSlots(date) {
        const container = document.getElementById('slotsContainer');
        const dateLabel = document.getElementById('selectedDateLabel');
        
        dateLabel.innerText = new Date(date).toDateString();
        container.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

        // Updated Route to Shared API
        fetch(`{{ route('api.day_details') }}?date=${date}&doctor_id=${selectedDoctorId}`)
            .then(r => r.json())
            .then(data => {
                container.innerHTML = '';
                
                if (data.status === 'closed') {
                    container.innerHTML = '<div class="alert alert-warning small m-2">Doctor not available.</div>';
                    return;
                }

                let hasSlots = false;
                data.slots.forEach(slot => {
                    if (slot.type === 'available') {
                        hasSlots = true;
                        let btn = document.createElement('button');
                        btn.className = 'list-group-item list-group-item-action text-center text-success font-weight-bold py-2 slot-btn';
                        btn.innerHTML = `${slot.time_label}`;
                        btn.type = 'button'; // Prevent form submit
                        btn.onclick = function() { selectTime(this, date, slot.raw_time, slot.time_label); };
                        container.appendChild(btn);
                    }
                });

                if (!hasSlots) container.innerHTML = '<div class="alert alert-secondary small m-2">Fully booked.</div>';
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="text-danger small text-center">Error loading slots. Please Login again.</div>';
            });
    }

    // STEP 4
    function selectTime(el, date, rawTime, label) {
        document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('active', 'bg-success', 'text-white'));
        el.classList.add('active', 'bg-success', 'text-white');

        document.getElementById('input_date').value = date;
        document.getElementById('input_time').value = rawTime;
        document.getElementById('sum_date').innerText = new Date(date).toDateString();
        document.getElementById('sum_time').innerText = label;

        document.getElementById('btnConfirm').disabled = false;
    }

    function unlockSection(cardId, headerId, oldClass, newClass) {
        const card = document.getElementById(cardId);
        const header = document.getElementById(headerId);
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
        header.classList.remove(oldClass);
        header.classList.add(newClass);
    }
</script>
@endpush