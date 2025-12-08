@extends('layouts.admin')

@section('content')
    <div class="container-fluid" x-data="vehicleForm()">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Vehicle Management</h1>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Vehicle Management</li>
                <li class="breadcrumb-item active">Edit Vehicle</li>
            </ol>
        </nav>

        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('admin.vehicles.update', $vehicle) }}" method="POST" enctype="multipart/form-data"
                    @submit.prevent="submitForm">
                    @csrf
                    @method('PUT')
                    @include('admin.vehicles._form')
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <style>
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: ">";
            color: #6c757d;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }

        .vehicle-type-images-container {
            min-height: 250px;
            border: 2px dashed #d3d7dd;
            border-radius: 10px;
            background: #f9fbfd;
            padding: 25px;
        }

        .vehicle-type-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .vehicle-type-image-item {
            border: 2px solid #e4e6e9;
            border-radius: 10px;
            padding: 8px;
            background: white;
            position: relative;
            transition: 0.25s;
        }

        .vehicle-type-image-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .vehicle-type-image-item.border-primary {
            border-color: #0d6efd !important;
        }

        .btn-save {
            background: linear-gradient(135deg, #F76B19 0%, #e55e14 100%);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.75rem;
            padding: 1rem 2.5rem;
            border: none;
            box-shadow: 0 6px 20px rgba(247, 107, 25, 0.3);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .vehicle-type-image-item img {
            border-radius: 4px;
        }

        .multiple-image-upload-area {
            margin-top: 0.5rem;
        }

        .multiple-image-upload-area h6 {
            font-size: 0.875rem;
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.75rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35, #f7931e);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script>
        function vehicleForm() {
            return {
                vehicleTypeId: '{{ old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') }}',
                selectedTypeData: null,

                init() {
                    this.$watch('vehicleTypeId', (value) => this.updateImages(value));
                    if (this.vehicleTypeId) {
                        this.updateImages(this.vehicleTypeId);
                    }
                },

                updateImages(id) {
                    if (!id) {
                        this.selectedTypeData = null;
                        return;
                    }
                    const select = document.getElementById('vehicle_type_id');
                    if (select) {
                        const option = Array.from(select.options).find(opt => opt.value == id);
                        if (option) {
                            try {
                                const images = JSON.parse(option.dataset.images || '[]');
                                const displayImage = option.dataset.displayImage;
                                this.selectedTypeData = {
                                    images,
                                    displayImage,
                                    text: option.text
                                };
                            } catch (e) {
                                console.error("Error parsing images", e);
                                this.selectedTypeData = {
                                    error: true,
                                    text: option.text
                                };
                            }
                        }
                    }
                },

                submitForm(event) {
                    const form = event.target;
                    if (window.multipleImageUploadInstance) {
                        const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
                        const formImageInput = form.querySelector('input[name="images[]"]');
                        if (selectedFiles.length > 0) {
                            const dt = new DataTransfer();
                            selectedFiles.forEach(file => dt.items.add(file));
                            formImageInput.files = dt.files;
                        }
                    }
                    form.submit();
                }
            }
        }
    </script>
@endpush
