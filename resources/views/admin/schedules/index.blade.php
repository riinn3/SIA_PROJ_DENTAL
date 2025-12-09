@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Doctor Schedule</h1>
                    
                    <div class="form-inline">
                        <label class="mr-2 text-gray-700 font-weight-bold">Filter by Doctor:</label>
                        <select id="doctorFilter" class="form-control shadow-sm mr-3">
                            <option value="">-- All Doctors --</option>
                            @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">Dr. {{ $doc->name }}</option>
                            @endforeach
                        </select>

                        <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#createScheduleModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Set Availability
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Selected Day Details</h6>
                </div>
                <div class="card-body" id="day-details-container">
                    <div class="text-center text-muted mt-5">
                        <i class="fas fa-calendar-day fa-3x mb-3"></i>
                        <p>Click a colored date on the calendar to view bookings.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="quickBookModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-calendar-plus mr-2"></i>Book Appointment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('admin.appointments.store_walkin') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="appointment_date" id="modalDate">
                        <input type="hidden" name="appointment_time" id="modalTime">
                        <input type="hidden" name="doctor_id" id="modalDoctor">

                        <div class="alert alert-info py-2">
                            Booking for: <strong id="displayDateSlot"></strong>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Patient Type</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" onclick="togglePatientType('existing')">
                                    <input type="radio" name="patient_type" value="existing" checked> Existing Record
                                </label>
                                <label class="btn btn-outline-primary" onclick="togglePatientType('new')">
                                    <input type="radio" name="patient_type" value="new"> New / Walk-In
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="existingPatientGroup">
                            <label>Select Patient</label>
                            <select name="user_id" class="form-control">
                                @foreach(\App\Models\User::where('role', 'patient')->orderBy('name')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="newPatientGroup" style="display: none;">
                            <div class="form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="new_name" class="form-control" placeholder="e.g. Juan Dela Cruz">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Email (Optional)</label>
                                    <input type="email" name="new_email" class="form-control" placeholder="juan@email.com">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="new_phone" class="form-control" placeholder="09123456789">
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label class="font-weight-bold">Service</label>
                                <select name="service_id" class="form-control" required>
                                    @foreach(\App\Models\Service::all() as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} - ₱{{ number_format($s->price) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-bold">Duration</label>
                                <select name="duration" class="form-control">
                                    <option value="1">1 Hour</option>
                                    <option value="2">2 Hours</option>
                                    <option value="3">3 Hours</option>
                                </select>
                            </div>
                        </div>
                        <small class="text-muted">Note: Extending duration will block adjacent slots automatically.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Booking</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var doctorSelect = document.getElementById('doctorFilter');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 650, 
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                // Fetch colored bars (Availability)
                events: function(info, successCallback, failureCallback) {
                    var doctorId = doctorSelect.value;
                    var url = "{{ route('admin.api.calendar') }}?doctor_id=" + doctorId + "&start=" + info.startStr + "&end=" + info.endStr;
                    fetch(url).then(r => r.json()).then(data => successCallback(data));
                },
                // CLICK LOGIC: Update Side Panel instead of Redirecting
                dateClick: function(info) {
                    // Visually select the day
                    document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                    info.dayEl.style.backgroundColor = '#f0f4ff'; // Light blue highlight
                    
                    fetchDayDetails(info.dateStr);
                },
                eventClick: function(info) {
                    // Also fetch details if they click the colored bar
                    fetchDayDetails(info.event.startStr);
                }
            });

            calendar.render();

            doctorSelect.addEventListener('change', function() {
                calendar.refetchEvents();
                document.getElementById('day-details-container').innerHTML = '<div class="text-center mt-5 text-muted">Select a date to view details</div>';
            });
        });

        // FETCH SIDE PANEL
        function fetchDayDetails(date) {
            const container = document.getElementById('day-details-container');
            const doctorId = document.getElementById('doctorFilter').value;
            
            if(!doctorId) {
                container.innerHTML = '<div class="alert alert-warning m-3">Please select a Doctor from the filter above first.</div>';
                return;
            }

            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

            fetch(`{{ route('admin.api.day_details') }}?date=${date}&doctor_id=${doctorId}`)
                .then(response => response.json())
                .then(data => {
                    let header = `<h5 class="font-weight-bold text-center mb-3 border-bottom pb-2 text-dark">${date}</h5>`;
                    
                    // SCENARIO 1: DOCTOR NOT WORKING (No Schedule)
                    if (data.status === 'closed') {
                        container.innerHTML = `
                            ${header}
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-gray-300"></i>
                                <h5>No Schedule Set</h5>
                                <p class="small">The doctor is not scheduled to work on this day.</p>
                                <a href="{{ route('admin.schedules.create') }}?date=${date}" class="btn btn-primary btn-sm px-4">
                                    <i class="fas fa-plus"></i> Set Availability
                                </a>
                            </div>
                        `;
                        return;
                    }

                    // SCENARIO 2: SHOW HOURLY SLOTS
                    let html = `${header}<div class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">`;

                    data.slots.forEach(slot => {
                        if (slot.status === 'booked') {
                            // LIGHT RED (Reserved)
                            html += `
                                <div class="list-group-item mb-2 rounded border-left-danger shadow-sm" style="background-color: #ffeef0;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="font-weight-bold text-danger">${slot.time}</span>
                                            <div class="small text-dark mt-1"><i class="fas fa-user"></i> ${slot.info.patient.name}</div>
                                            <div class="small text-muted">${slot.info.service.name}</div>
                                        </div>
                                        <span class="badge badge-danger">Reserved</span>
                                    </div>
                                </div>
                            `;
                        } else {
                            // LIGHT GREEN (Available)
                            html += `
                                <button onclick="openBookingModal('${date}', '${slot.raw_time}', '${slot.time}', '${slot.doctor_id}')" 
                                    class="list-group-item list-group-item-action mb-2 rounded border-left-success shadow-sm" style="background-color: #f0fff4;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold text-success">${slot.time}</span>
                                        <span class="badge badge-success px-3">Open</span>
                                    </div>
                                </button>
                            `;
                        }
                    });

                    html += '</div>';
                    container.innerHTML = html;
                });
        }

        // --- MODAL LOGIC ---
        function togglePatientType(type) {
            if(type === 'new') {
                document.getElementById('existingPatientGroup').style.display = 'none';
                document.getElementById('newPatientGroup').style.display = 'block';
            } else {
                document.getElementById('existingPatientGroup').style.display = 'block';
                document.getElementById('newPatientGroup').style.display = 'none';
            }
        }

        function openBookingModal(date, rawTime, displayTime, doctorId) {
            document.getElementById('modalDate').value = date;
            document.getElementById('modalTime').value = rawTime;
            document.getElementById('modalDoctor').value = doctorId;
            document.getElementById('displayDateSlot').innerText = date + ' @ ' + displayTime;
            $('#quickBookModal').modal('show');
        }
    </script>
@endpush