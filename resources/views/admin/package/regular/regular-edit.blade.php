@extends('layouts.admin')
@section('title', 'Edit Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">

    <style>
        .card {
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2c3e50;
            font-size: 1.25rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19, #e55e14);
            color: #fff;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: .3s;
            position: relative;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(247, 107, 25, 0.4);
        }

        .btn-save:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }

        .btn-save.btn-loading {
            pointer-events: none;
            color: transparent !important;
        }

        .btn-save.btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid transparent;
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .table-warning {
            background-color: #fff3cd !important;
        }

        .weekend-badge {
            font-size: 0.7em;
            vertical-align: middle;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .existing-image {
            position: relative;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            width: 100px;
            height: 100px;
        }

        .existing-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <main class="mt-4">
        <header class="d-flex justify-content-between align-items-center page-header mb-4">
            <div>
                <h3>Edit Regular Package</h3>
                <p class="breadcrumb-custom">
                    <i class="bi bi-home me-1"></i>
                    Package Management > Edit Package
                </p>
            </div>
            <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Packages
            </a>
        </header>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div x-data="packageFormHandler()">
            <form x-ref="packageForm" @submit.prevent="handleSubmit" method="POST"
                action="{{ route('admin.packages.regular.update', $package) }}" enctype="multipart/form-data">
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
                    requiredFields.forEach(name => {
                        const el = form.querySelector(`[name="${name}"]`);
                        if (el) {
                            if (!el.value.trim()) {
                                isValid = false;
                                el.classList.add('is-invalid');
                            } else {
                                el.classList.remove('is-invalid');
                            }
                        }
                    });
                    if (!isValid) {
                        alert('Please fill in all required fields.');
                    }
                    return isValid;
                },
                attachImages() {
                    // Access the Alpine instance attached to the DOM
                    const container = document.getElementById('package-image-uploader');
                    const uploader = container ? container._x_uploader : null;

                    if (uploader) {
                        try {
                            // Native Alpine access - getSelectedFiles() defined in component
                            const files = uploader.getSelectedFiles();

                            if (files && files.length > 0) {
                                const dataTransfer = new DataTransfer();
                                files.forEach(file => {
                                    if (file instanceof File) dataTransfer.items.add(file);
                                });
                                this.$refs.packageImagesInput.files = dataTransfer.files;
                                console.log('Attached ' + files.length + ' files to form input.');
                            }
                        } catch (error) {
                            console.error('Error attaching images:', error);
                            alert('Error preparing images for upload.');
                        }
                    } else {
                        console.warn('Image Uploader component not found.');
                    }
                },
                handleSubmit() {
                    this.attachImages();
                    if (this.validateForm()) {
                        this.$refs.packageForm.submit();
                    }
                }
            }));
        });
    </script>
@endpush
