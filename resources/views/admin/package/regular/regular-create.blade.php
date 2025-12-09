@extends('layouts.admin')
@section('title', isset($package) ? 'Edit Regular Package' : 'Add Regular Package')

@push('styles')
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center mb-4">
            <h3>{{ isset($package) ? 'Edit Package' : 'Add Package' }}</h3>
            <a href="#" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </header>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div x-data="packageFormHandler()">
            <form x-ref="packageForm" @submit.prevent="handleSubmit"
                action="{{ isset($package) ? route('admin.packages.regular.update', $package) : route('admin.packages.regular.store') }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @if (isset($package))
                    @method('PUT')
                @endif

                @include('admin.package.regular.regular_form')
            </form>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packageFormHandler', () => ({
                init() {
                    // Component self-initializes
                },
                validateForm() {
                    const form = this.$refs.packageForm;
                    const requiredFields = ['packageName', 'packageType', 'displayStartingPrice',
                        'minParticipant', 'maxParticipant'
                    ];
                    let isValid = true;
                    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove(
                        'is-invalid'));

                    requiredFields.forEach(name => {
                        const el = form.querySelector(`[name="${name}"]`);
                        if (el) {
                            if (!el.value.trim()) {
                                isValid = false;
                                el.classList.add('is-invalid');
                            }
                        }
                    });

                    if (!isValid) {
                        alert('Please fill in all required fields.');
                    }
                    return isValid;
                },
                handleSubmit() {
                    if (this.validateForm()) {
                        this.$refs.packageForm.submit();
                    }
                }
            }));
        });
    </script>
