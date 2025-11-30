@extends('layouts.admin')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Archived Services</h1>
        <a href="{{ route('admin.services.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Active List
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-left-success" role="alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow mb-4 border-left-danger">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-danger">Inactive Treatments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($archivedServices as $service)
                        <tr class="text-muted">
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->deleted_at->format('M d, Y') }}</td>
                            <td>
                                <form action="{{ route('admin.services.restore', $service->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Restore">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                </form>

                                <form action="{{ route('admin.services.forceDelete', $service->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this service? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete Forever">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($archivedServices->isEmpty())
                    <p class="text-center text-muted mt-3">No archived services found.</p>
                @endif
            </div>
        </div>
    </div>
@endsection