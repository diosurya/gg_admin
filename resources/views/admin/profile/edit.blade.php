@extends('layouts.app')

@section('title', 'Edit Profile')

@php
    $pageTitle = 'Edit Profile';
    $breadcrumbs = [
        ['title' => 'Settings', 'url' => ' '],
        ['title' => 'My Profile']
    ];
@endphp

@push('styles')
<link href="{{ asset('css/plugins/summernote/summernote-bs4.css') }}" rel="stylesheet">
{{-- Font Awesome (untuk ikon eye) --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" />
@endpush

@section('content')
    <h2 class="mb-4">Edit Profile</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            {{-- PROFILE FORM (LEFT) --}}
            <div class="col-lg-6">
                <form action="{{ route('admin.settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-header"><h5 class="card-title mb-0">Profile</h5></div>
                        <div class="card-body">
                            {{-- First Name --}}
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ old('first_name', $user->first_name) }}" required>
                            </div>

                            {{-- Last Name --}}
                            <div class="mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ old('last_name', $user->last_name) }}">
                            </div>

                            {{-- Email --}}
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                            </div>

                            {{-- Phone --}}
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>

                            {{-- Avatar --}}
                            <div class="mb-3">
                                <label class="form-label">Avatar</label><br>
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" width="100" class="mb-2 rounded shadow">
                                @endif
                                <input type="file" name="avatar" class="form-control">
                            </div>

                            {{-- Date of Birth --}}
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $user->date_of_birth) }}">
                            </div>

                            {{-- Gender --}}
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="">-- Select --</option>
                                    <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary px-4">Save Profile</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- CHANGE PASSWORD FORM (RIGHT) --}}
            <div class="col-lg-6">
                <form action="{{ route('admin.settings.profile.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4">
                        <div class="card-header"><h5 class="card-title mb-0">Change Password</h5></div>
                        <div class="card-body">
                            {{-- Current Password --}}
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <div class="input-group">
                                    <input type="password" id="current-password" name="current_password" class="form-control" required aria-label="Current password">
                                    <button class="btn btn-info" type="button" onclick="togglePassword('current-password', this)" aria-label="Toggle current password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- New Password --}}
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" id="new-password" name="password" class="form-control" required aria-label="New password">
                                    <button type="button" class="btn btn-danger" onclick="generatePassword()" title="Generate strong password">
                                        Generate
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="togglePassword('new-password', this)" aria-label="Toggle new password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                </div>

                                <div class="progress mt-2" style="height: 8px;">
                                    <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0;"></div>
                                </div>
                                <small id="password-strength-text" class="form-text text-muted fw-bold"></small>
                            </div>

                            {{-- Confirm Password --}}
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" id="confirm-password" name="password_confirmation" class="form-control" required aria-label="Confirm password">
                                    <button class="btn btn-info" type="button" onclick="togglePassword('confirm-password', this)" aria-label="Toggle confirm password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="submit" class="btn btn-warning px-4">Update Password</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div> {{-- /row --}}
    </div> {{-- /wrapper --}}

    <hr class="my-5">

    {{-- SCRIPTS: generate, strength meter, toggle --}}
    <script>
        // Utility: shuffle array
        function shuffleArray(a) {
            for (let i = a.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [a[i], a[j]] = [a[j], a[i]];
            }
            return a;
        }

        // Evaluate strength (0-5)
        function evaluateStrength(password) {
            let score = 0;
            if (!password) return 0;
            if (password.length >= 8) score += 1;
            if (/[A-Z]/.test(password)) score += 1;
            if (/[a-z]/.test(password)) score += 1;
            if (/[0-9]/.test(password)) score += 1;
            if (/[^A-Za-z0-9]/.test(password)) score += 1;
            return score;
        }

        function updateStrengthMeter(password) {
            const strengthBar = document.getElementById('password-strength-bar');
            const strengthText = document.getElementById('password-strength-text');
            const score = evaluateStrength(password);
            const strengthLevels = ["Very Weak", "Weak", "Medium", "Strong", "Very Strong"];
            const colors = ["#dc3545", "#fd7e14", "#ffc107", "#0d6efd", "#198754"];

            const width = score * 20;
            strengthBar.style.width = width + "%";
            strengthBar.style.backgroundColor = colors[Math.max(0, score - 1)] || "#ddd";
            strengthText.textContent = score ? strengthLevels[score - 1] : "";

            // little animation
            try {
                strengthBar.animate([
                    { transform: "scaleX(0.98)" },
                    { transform: "scaleX(1)" }
                ], { duration: 220, iterations: 1 });
            } catch (e) { /* ignore if not supported */ }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const newPasswordInput = document.getElementById('new-password');
            const confirmPasswordInput = document.getElementById('confirm-password');

            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function () {
                    updateStrengthMeter(this.value);
                });
            }

            // Generate password ensuring at least one uppercase, lowercase, digit, and symbol
            window.generatePassword = function(length = 16) {
                const lower = "abcdefghijklmnopqrstuvwxyz";
                const upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                const digits = "0123456789";
                const symbols = "!@#$%^&*()_-+=<>?";

                // ensure one of each
                const required = [
                    lower.charAt(Math.floor(Math.random() * lower.length)),
                    upper.charAt(Math.floor(Math.random() * upper.length)),
                    digits.charAt(Math.floor(Math.random() * digits.length)),
                    symbols.charAt(Math.floor(Math.random() * symbols.length))
                ];

                // fill the rest
                const all = lower + upper + digits + symbols;
                const rest = [];
                for (let i = 0; i < (length - required.length); i++) {
                    rest.push(all.charAt(Math.floor(Math.random() * all.length)));
                }

                // combine & shuffle to avoid predictable placement
                const passwordArr = shuffleArray(required.concat(rest));
                const password = passwordArr.join('');

                // set values
                if (newPasswordInput) newPasswordInput.value = password;
                if (confirmPasswordInput) confirmPasswordInput.value = password;

                // update strength meter
                updateStrengthMeter(password);

                // focus new password briefly so user sees it
                if (newPasswordInput) {
                    newPasswordInput.focus();
                    // optional: briefly flash the input
                    newPasswordInput.classList.add('border-2');
                    setTimeout(() => newPasswordInput.classList.remove('border-2'), 600);
                }
            };

            // Toggle show/hide password
            window.togglePassword = function(fieldId, btn) {
                const input = document.getElementById(fieldId);
                if (!input || !btn) return;
                const icon = btn.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            };
        });
    </script>
@endsection
