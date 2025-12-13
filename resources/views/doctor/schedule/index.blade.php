@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Schedule Manager</h1>
    </div>

    <div class="row">
        {{-- CALENDAR CARD --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Availability</h6>
                </div>
                <div class="card-body p-0">
                    <div id="calendar" class="p-3"></div>
                </div>
            </div>
        </div>

        {{-- ACTIONS CARD --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Day Actions</h6>
                </div>
                <div class="card-body">
                    <h5 id="selectedDateLabel" class="font-weight-bold text-center mb-4">Select a Date</h5>
                    
                    <div id="actionContainer" style="display:none;">
                        <form id="scheduleForm">
                            @csrf
                            <input type="hidden" name="date" id="inputDate">
                            
                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">START TIME</label>
                                <input type="time" name="start_time" id="inputStart" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold text-muted">END TIME</label>
                                <input type="time" name="end_time" id="inputEnd" class="form-control">
                            </div>

                            <div class="custom-control custom-switch mb-4">
                                <input type="checkbox" class="custom-control-input" id="dayOffSwitch" name="is_day_off">
                                <label class="custom-control-label text-danger font-weight-bold" for="dayOffSwitch">Mark as Day Off</label>
                                <small class="form-text text-muted">This will prevent new bookings.</small>
                            </div>

                            <button type="button" onclick="saveSchedule()" class="btn btn-primary btn-block">
                                Update Availability
                            </button>
                        </form>
                    </div>
                    
                    <div id="placeholder" class="text-center text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3 text-gray-300"></i>
                        <p>Click a date on the calendar to modify your hours.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
    var calendar;
    var doctorId = "{{ Auth::id() }}"; 

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 500,
            selectable: true,
            events: `{{ route('api.calendar') }}?doctor_id=${doctorId}`,
            
            dateClick: function(info) {
                document.querySelectorAll('.fc-daygrid-day').forEach(el => el.style.backgroundColor = '');
                info.dayEl.style.backgroundColor = '#eaecf4';

                document.getElementById('selectedDateLabel').innerText = new Date(info.dateStr).toDateString();
                document.getElementById('inputDate').value = info.dateStr;
                
                document.getElementById('placeholder').style.display = 'none';
                document.getElementById('actionContainer').style.display = 'block';

                document.getElementById('inputStart').value = "09:00";
                document.getElementById('inputEnd').value = "17:00";
                document.getElementById('dayOffSwitch').checked = false;
            }
        });
        calendar.render();
    });

    function saveSchedule() {
        const formData = new FormData(document.getElementById('scheduleForm'));
        formData.set('is_day_off', document.getElementById('dayOffSwitch').checked ? 1 : 0);

        fetch("{{ route('doctor.schedule.updateDate') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok) {
                alert("Schedule updated!");
                calendar.refetchEvents();
            } else {
                alert(data.error || "Error updating schedule.");
            }
        })
        .catch(err => alert("Network error."));
    }
</script>
@endpush