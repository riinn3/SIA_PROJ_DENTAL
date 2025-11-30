@extends('layouts.admin')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Services List</h1>
        <div>
            <a href="{{ route('admin.services.archives') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm mr-2">
                <i class="fas fa-archive fa-sm text-white-50"></i> Archives
            </a>
            <a href="{{ route('admin.services.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Add New Service
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Dental Treatments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr>
                            <td class="font-weight-bold">{{ $service->name }}</td>
                            <td>{{ $service->description ?? 'No description' }}</td>
                            <td>{{ $service->duration_minutes }} mins</td>
                            <td class="text-success font-weight-bold">₱{{ number_format($service->price, 2) }}</td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>
                                <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-info btn-sm btn-circle" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>

                                <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Archive this service?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-warning btn-sm btn-circle" title="Archive">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection