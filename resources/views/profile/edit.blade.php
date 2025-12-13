@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Account Settings</h1>

    <div class="row">
        
        {{-- 1. UPDATE INFO CARD --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Information</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        {{-- Optional: Add Phone if your DB has it --}}
                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            Save Changes
                        </button>

                        @if (session('status') === 'profile-updated')
                            <span class="small text-success ml-3 fade-out"><i class="fas fa-check"></i> Saved!</span>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. UPDATE PASSWORD CARD --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4 border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Security & Password</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">Current Password</label>
                            <input type="password" name="current_password" class="form-control" required>
                            @error('current_password', 'updatePassword') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">New Password</label>
                            <input type="password" name="password" class="form-control" required>
                            @error('password', 'updatePassword') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group">
                            <label class="small mb-1 font-weight-bold">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning btn-sm px-4 text-white">
                            Update Password
                        </button>

                        @if (session('status') === 'password-updated')
                            <span class="small text-success ml-3 fade-out"><i class="fas fa-check"></i> Updated!</span>
                        @endif
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Simple fade out for success messages
    setTimeout(function() {
        $('.fade-out').fadeOut('slow');
    }, 3000);
</script>
@endsection