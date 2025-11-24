@extends('layouts.admin')

@section('title', 'Settings - Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Settings</h2>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
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
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock me-2"></i>Password Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-key me-2"></i>Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        

        

        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-primary me-2">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                        <a href="{{ route('admin.reservation-dashboard') }}" class="btn btn-outline-secondary">
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
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>System Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">PHP Version</small>
                            <div class="fw-bold">{{ phpversion() }}</div>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Laravel Version</small>
                            <div class="fw-bold">{{ app()->version() }}</div>
                        </div>
                    </div>
                    <hr>
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
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthIndicator(strength);
        });
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    const feedback = document.querySelector('.form-text');
    if (feedback) {
        const messages = [
            'Very Weak',
            'Weak', 
            'Fair',
            'Good',
            'Strong'
        ];
        const colors = [
            'text-danger',
            'text-warning',
            'text-info',
            'text-primary',
            'text-success'
        ];
        
        feedback.className = `form-text ${colors[strength - 1] || 'text-muted'}`;
        feedback.textContent = `Password Strength: ${messages[strength - 1] || 'Very Weak'}`;
    }
}
</script>
@endpush
