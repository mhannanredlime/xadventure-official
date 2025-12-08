@extends('layouts.admin')

@section('title', 'Settings - Admin')

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">Settings</h2>
                </div>
            </div>
        </div>



        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">

            <!-- Password Settings -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Password Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.settings.password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                    id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required>
                                <div class="form-text text-muted">Password must be at least 8 characters long.</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password <span
                                        class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn jatio-bg-color text-white">
                                    <i class="bi bi-key me-2"></i>Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & System Info -->
            <div class="col-lg-6 mb-4">

                <!-- Quick Actions -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-primary">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-clipboard-check me-2"></i>Dashboard
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-info">
                                <i class="bi bi-graph-up me-2"></i>Reports
                            </a>
                            <a href="{{ route('admin.calendar.index') }}" class="btn btn-outline-success">
                                <i class="bi bi-calendar-event me-2"></i>Calendar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6">
                                <small class="text-muted">PHP Version</small>
                                <div class="fw-bold">{{ phpversion() }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Laravel Version</small>
                                <div class="fw-bold">{{ app()->version() }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Environment</small>
                                <div class="fw-bold">{{ config('app.env') }}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Debug Mode</small>
                                <div class="fw-bold">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                });
            }, 5000);

            // Password Strength Indicator
            const passwordInput = document.getElementById('password');
            const feedback = passwordInput ? passwordInput.nextElementSibling : null;

            if (passwordInput && feedback) {
                passwordInput.addEventListener('input', () => {
                    const password = passwordInput.value;
                    const strength = calculatePasswordStrength(password);
                    updatePasswordFeedback(feedback, strength);
                });
            }

            function calculatePasswordStrength(password) {
                let score = 0;
                if (password.length >= 8) score++;
                if (/[a-z]/.test(password)) score++;
                if (/[A-Z]/.test(password)) score++;
                if (/[0-9]/.test(password)) score++;
                if (/[^A-Za-z0-9]/.test(password)) score++;
                return score;
            }

            function updatePasswordFeedback(element, score) {
                const messages = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const colors = ['text-danger', 'text-warning', 'text-info', 'text-primary', 'text-success'];

                element.className = `form-text ${colors[score-1] || 'text-muted'}`;
                element.textContent = `Password Strength: ${messages[score-1] || 'Very Weak'}`;
            }

        });
    </script>
@endpush
