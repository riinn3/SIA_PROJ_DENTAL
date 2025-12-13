@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">My Treated Patients</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Patient List</h6>
        </div>
        <div class="card-body">
            @if($patients->isEmpty())
                <p class="text-muted text-center py-4">You haven't completed any appointments yet.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                            <tr>
                                <td class="font-weight-bold">{{ $patient->name }}</td>
                                <td>{{ $patient->email }}</td>
                                <td>{{ $patient->phone ?? 'N/A' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info disabled" title="View History (Coming Soon)">
                                        <i class="fas fa-file-medical-alt"></i> Records
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection