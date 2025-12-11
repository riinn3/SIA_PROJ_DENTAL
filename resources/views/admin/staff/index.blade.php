@extends('layouts.admin')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Staff Management</h1>
    <a href="{{ route('admin.staff.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-user-plus fa-sm text-white-50"></i> Add New Staff
    </a>
</div>

@if(session('success')) <div class="alert alert-success border-left-success">{{ session('success') }}</div> @endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <ul class="nav nav-pills card-header-pills">
            <li class="nav-item">
                <a class="nav-link {{ $view == 'active' ? 'active' : '' }}" href="{{ route('admin.staff.index') }}">Active Staff</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $view == 'archived' ? 'active bg-danger text-white' : 'text-danger' }}" href="{{ route('admin.staff.index', ['view' => 'archived']) }}">
                    <i class="fas fa-user-slash mr-1"></i> Deactivated
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr><th>Name</th><th>Role</th><th>Email</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($staff as $user)
                    <tr>
                        <td class="font-weight-bold">{{ $user->name }}</td>
                        <td><span class="badge {{ $user->role == 'admin' ? 'badge-danger' : 'badge-info' }}">{{ ucfirst($user->role) }}</span></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($view == 'archived')
                                <form action="{{ route('admin.staff.restore', $user->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-success btn-sm"><i class="fas fa-trash-restore"></i> Restore</button>
                                </form>
                            @else
                                <form action="{{ route('admin.staff.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Deactivate user?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm btn-circle"><i class="fas fa-user-times"></i></button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white d-flex justify-content-end">
        {{ $staff->appends(['view' => $view])->links() }}
    </div>
</div>
@endsection