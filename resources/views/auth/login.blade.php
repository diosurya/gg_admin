@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div>
            <h1 class="logo-name">GG</h1>
        </div>
        
        <h3>Welcome to Gudang Grosiran</h3>
        
        <p>{{ config('app.description', 'Perfectly designed and precisely prepared admin theme with comprehensive management features.') }}</p>
        
        <p>Please login to access the admin panel.</p>

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Message --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        {{-- Login Form --}}
        <form class="m-t" method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            
            <div class="form-group">
                <input type="email" 
                       name="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       placeholder="Email Address" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus>
                
                @error('email')
                    <div class="invalid-feedback d-block">
                        <small class="text-danger">{{ $message }}</small>
                    </div>
                @enderror
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Password" 
                       required>
                
                @error('password')
                    <div class="invalid-feedback d-block">
                        <small class="text-danger">{{ $message }}</small>
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <div class="checkbox i-checks">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <i></i> Remember me
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary block full-width m-b">
                <i class="fa fa-sign-in"></i> Login
            </button>
            
            {{-- Forgot Password Link --}}
            @if(Route::has('admin.password.request'))
                <a href="{{ route('admin.password.request') }}">
                    <small>Forgot password?</small>
                </a>
            @endif
            
            {{-- Registration Link (if enabled) --}}
            @if(config('auth.registration_enabled', false) && Route::has('admin.register'))
                <p class="text-muted text-center">
                    <small>Don't have an account?</small>
                </p>
                <a class="btn btn-sm btn-white btn-block" href="{{ route('admin.register') }}">
                    <i class="fa fa-user-plus"></i> Create an account
                </a>
            @endif
        </form>
        
        <p class="m-t">
            <small>{{ config('app.name') }} &copy; {{ date('Y') }} | Version {{ config('app.version', '1.0.0') }}</small>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Form validation
    $('form').on('submit', function() {
        var email = $('input[name="email"]').val();
        var password = $('input[name="password"]').val();
        
        if (!email || !password) {
            alert('Please fill in all required fields');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html(
            '<i class="fa fa-spinner fa-spin"></i> Logging in...'
        ).prop('disabled', true);
    });
});
</script>
@endpush