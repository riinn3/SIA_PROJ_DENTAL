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
                    <h5 class="modal-title">Book Appointment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <form action="{{ route('admin.appointments.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="appointment_date" id="modalDate">
                        <input type="hidden" name="appointment_time" id="modalTime">
                        <input type="hidden" name="doctor_id" id="modalDoctor">

                        <p>Booking for: <strong id="displayDateSlot"></strong></p>

                        <div class="form-group">
                            <label>Patient</label>
                            <select name="user_id" class="form-control" required>
                                @foreach(\App\Models\User::where('role', 'patient')->get() as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Service</label>
                            <select name="service_id" class="form-control" required>
                                @foreach(\App\Models\Service::all() as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} - ₱{{ $s->price }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
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
                height: 650, // <--- FIXED HEIGHT (Makes it smaller)
                contentHeight: 600,
                aspectRatio: 1.5,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                
                // Fetch events from API
                events: function(info, successCallback, failureCallback) {
                    var doctorId = doctorSelect.value;
                    var url = "{{ route('admin.api.calendar') }}?doctor_id=" + doctorId + "&start=" + info.startStr + "&end=" + info.endStr;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => successCallback(data))
                        .catch(error => failureCallback(error));
                },

                // --- INTERACTION LOGIC ---

                // 1. Clicking an EXISTING Event (Green/Red Block) -> Show Details
                eventClick: function(info) {
                    // Highlight the selected day visually (optional style)
                    document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                    info.el.closest('.fc-daygrid-day').style.backgroundColor = '#f8f9fc';

                    var dateStr = info.event.startStr; 
                    fetchDayDetails(dateStr);
                },

                // 2. Clicking an EMPTY Day -> Create Schedule
                dateClick: function(info) {
                    // Redirect to create page with date pre-filled
                    var url = "{{ route('admin.schedules.create') }}?date=" + info.dateStr;
                    window.location.href = url;
                }
            });

            calendar.render();

            // Refresh when doctor filter changes
            doctorSelect.addEventListener('change', function() {
                calendar.refetchEvents();
            });
        });

        // Fetch Sidebar Details
        function fetchDayDetails(date) {
            const container = document.getElementById('day-details-container');
            const doctorId = document.getElementById('doctorFilter').value;
            
            container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Checking slots...</p></div>';

            fetch(`{{ route('admin.api.day_details') }}?date=${date}&doctor_id=${doctorId}`)
                .then(response => response.json())
                .then(data => {
                    // If no schedule exists (Redundant check, but good for safety)
                    if (data.status === 'closed') {
                        container.innerHTML = `
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-door-closed fa-3x mb-3"></i>
                                <h5>Doctor Not Available</h5>
                                <p>No schedule set for this date.</p>
                                <a href="{{ route('admin.schedules.create') }}?date=${date}" class="btn btn-sm btn-primary mt-2">Set Availability</a>
                            </div>
                        `;
                        return;
                    }

                    // Build the Slots List
                    let html = `<h5 class="font-weight-bold text-center mb-3 border-bottom pb-2 text-dark">${date}</h5>
                                <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">`;

                    data.slots.forEach(slot => {
                        if (slot.status === 'booked') {
                            // RED SLOT (Booked) - Admin can view details/cancel
                            html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center bg-light text-muted p-2">
                                    <div>
                                        <span class="font-weight-bold">${slot.time}</span>
                                        <br><small class="text-primary"><i class="fas fa-user"></i> ${slot.info.patient.name}</small>
                                        <br><small class="text-xs">${slot.info.service.name}</small>
                                    </div>
                                    <span class="badge badge-danger">Booked</span>
                                </div>
                            `;
                        } else {
                            // GREEN SLOT (Available) - Admin can book walk-in
                            html += `
                                <button onclick="openBookingModal('${date}', '${slot.raw_time}', '${slot.time}', '${slot.doctor_id}')" 
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-2">
                                    <span class="font-weight-bold text-success">${slot.time}</span>
                                    <span class="badge badge-success px-2 py-1">Available</span>
                                </button>
                            `;
                        }
                    });

                    html += '</div>';
                    container.innerHTML = html;
                });
        }

        function openBookingModal(date, rawTime, displayTime, doctorId) {
            if(!doctorId) {
                alert("Please select a specific Doctor from the dropdown filter first.");
                return;
            }
            document.getElementById('modalDate').value = date;
            document.getElementById('modalTime').value = rawTime;
            document.getElementById('modalDoctor').value = doctorId;
            document.getElementById('displayDateSlot').innerText = date + ' at ' + displayTime;
            
            $('#quickBookModal').modal('show');
        }
    </script>

    <style>
        .fc-daygrid-day { cursor: pointer; transition: background 0.2s; }
        .fc-daygrid-day:hover { background-color: #f8f9fc; }
        .fc-event { cursor: pointer; }
    </style>
@endpush