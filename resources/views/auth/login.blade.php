@extends('layouts.guest')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="text-gray-900 font-weight-bold">Welcome Back!</h4>
                        <p class="text-muted small">Login to manage your appointments</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label class="small text-muted font-weight-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill px-3 py-2" required autofocus placeholder="Enter Email...">
                            @error('email') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-4 position-relative">
                            <label class="small text-muted font-weight-bold">Password</label>
                            {{-- Adjusted padding (py-2) and icon position (top: 32px) --}}
                            <input type="password" name="password" id="password" class="form-control rounded-pill px-3 py-2" required placeholder="Password" style="padding-right: 40px;">
                            
                            <button type="button" onclick="togglePassword('password')" class="btn btn-link position-absolute" style="top: 32px; right: 10px; text-decoration: none; color: #aaa;">
                                <i class="fas fa-eye" id="password-icon"></i>
                            </button>
                            
                            @error('password') <small class="text-danger pl-3">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group mb-4 pl-1">
                            <div class="custom-control custom-checkbox small">
                                <input type="checkbox" class="custom-control-input" id="remember_me" name="remember">
                                <label class="custom-control-label" for="remember_me">Remember Me</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm py-2">
                            Login
                        </button>
                    </form>

                    <hr>
                    <div class="text-center small">
                        <a href="{{ route('password.request') }}" class="text-muted">Forgot Password?</a>
                    </div>
                    <div class="text-center small mt-2">
                        <a href="{{ route('register') }}" class="text-primary font-weight-bold">Create an Account!</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection