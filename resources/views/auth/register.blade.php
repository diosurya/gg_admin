@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="middle-box text-center loginscreen animated fadeInDown">
    <div>
        <div>
            <h1 class="logo-name">{{ config('app.short_name', 'GG') }}</h1>
        </div>
        
        <h3>Create Admin Account</h3>
        
        <p>Fill in the details below to create your admin account.</p>

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

        {{-- Registration Form --}}
        <form class="m-t" method="POST" action="{{ route('admin.register.post') }}">
            @csrf
            
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="text" 
                               name="first_name" 
                               class="form-control @error('first_name') is-invalid @enderror" 
                               placeholder="First Name" 
                               value="{{ old('first_name') }}" 
                               required>
                        
                        @error('first_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <input type="text" 
                               name="last_name" 
                               class="form-control @error('last_name') is-invalid @enderror" 
                               placeholder="Last Name" 
                               value="{{ old('last_name') }}" 
                               required>
                        
                        @error('last_name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <input type="text" 
                       name="username" 
                       class="form-control @error('username') is-invalid @enderror" 
                       placeholder="Username" 
                       value="{{ old('username') }}" 
                       required>
                
                @error('username')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <input type="email" 
                       name="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       placeholder="Email Address" 
                       value="{{ old('email') }}" 
                       required>
                
                @error('email')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <input type="tel" 
                       name="phone" 
                       class="form-control @error('phone') is-invalid @enderror" 
                       placeholder="Phone Number (Optional)" 
                       value="{{ old('phone') }}">
                
                @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <select name="role" class="form-control @error('role') is-invalid @enderror" required>
                    <option value="">Select Role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="author" {{ old('role') == 'author' ? 'selected' : '' }}>Author</option>
                </select>
                
                @error('role')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>
            
            <div class="form-group">
                <input type="password" 
                       name="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       placeholder="Password" 
                       required>
                
                @error('password')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <input type="password" 
                       name="password_confirmation" 
                       class="form-control" 
                       placeholder="Confirm Password" 
                       required>
            </div>

            <div class="form-group">
                <div class="checkbox i-checks">
                    <label>
                        <input type="checkbox" required>
                        <i></i> I agree to the <a href="#" target="_blank">Terms of Service</a>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary block full-width m-b">
                <i class="fa fa-user-plus"></i> Create Account
            </button>
            
            <p class="text-muted text-center">
                <small>Already have an account?</small>
            </p>
            <a class="btn btn-sm btn-white btn-block" href="{{ route('admin.login') }}">
                <i class="fa fa-sign-in"></i> Login to existing account
            </a>
        </form>
        
        <p class="m-t">
            <small>{{ config('app.name') }} &copy; {{ date('Y') }}</small>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    // Password strength indicator
    $('input[name="password"]').on('keyup', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]+/)) strength++;
        if (password.match(/[A-Z]+/)) strength++;
        if (password.match(/[0-9]+/)) strength++;
        if (password.match(/[^a-zA-Z0-9]+/)) strength++;
        
        var strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var strengthColor = ['#ff0000', '#ff6600', '#ffcc00', '#99cc00', '#00cc00'];
        
        if (password.length > 0) {
            if (!$('#password-strength').length) {
                $(this).after('<small id="password-strength"></small>');
            }
            $('#password-strength')
                .text('Password strength: ' + strengthText[Math.min(strength, 4)])
                .css('color', strengthColor[Math.min(strength, 4)]);
        } else {
            $('#password-strength').remove();
        }
    });

    // Form validation
    $('form').on('submit', function() {
        var password = $('input[name="password"]').val();
        var confirmPassword = $('input[name="password_confirmation"]').val();
        
        if (password !== confirmPassword) {
            alert('Password and confirmation do not match');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html(
            '<i class="fa fa-spinner fa-spin"></i> Creating Account...'
        ).prop('disabled', true);
    });
});
</script>
@endpush