@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            {{-- PAGE HEADER --}}
            <div class="mb-4">
                <h1 class="h3 text-gray-800 font-weight-bold">My Profile</h1>
                <p class="text-muted">Manage your account settings and security preferences.</p>
            </div>

            {{-- SUCCESS MESSAGE --}}
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-left-success" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    @if(session('status') === 'profile-updated') Profile information updated. @endif
                    @if(session('status') === 'password-updated') Password updated successfully. @endif
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- MAIN CARD --}}
            <div class="card shadow-sm border-0 mb-5">
                <div class="card-body p-5">
                    
                    {{-- 1. PROFILE INFO --}}
                    <h5 class="font-weight-bold text-dark mb-4 pb-2 border-bottom">
                        <i class="fas fa-user-circle text-primary mr-2"></i> Personal Information
                    </h5>
                    
                    <form method="post" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch')
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Full Name</label>
                                <input type="text" name="name" class="form-control rounded-pill" value="{{ old('name', $user->name) }}" required>
                                @error('name') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Email Address</label>
                                <input type="email" name="email" class="form-control rounded-pill" value="{{ old('email', $user->email) }}" required>
                                @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="form-row">
                             <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Phone Number</label>
                                <input type="text" name="phone" class="form-control rounded-pill" value="{{ old('phone', $user->phone) }}">
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>

                    <div class="py-4"></div>

                    {{-- 2. SECURITY --}}
                    <h5 class="font-weight-bold text-dark mb-4 pb-2 border-bottom">
                        <i class="fas fa-lock text-primary mr-2"></i> Security
                    </h5>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label class="small font-weight-bold text-gray-600">Current Password</label>
                            <input type="password" name="current_password" class="form-control rounded-pill w-50" required>
                            @error('current_password', 'updatePassword') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">New Password</label>
                                <input type="password" name="password" class="form-control rounded-pill" required>
                                @error('password', 'updatePassword') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-gray-600">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control rounded-pill" required>
                            </div>
                        </div>

                        <div class="text-right mt-3">
                            <button type="submit" class="btn btn-secondary btn-sm px-4 rounded-pill font-weight-bold shadow-sm">
                                Update Password
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection