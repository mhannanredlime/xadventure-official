@extends('layouts.admin')

@section('title', 'Add Regular Package')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
@endpush

@section('content')
    <main class="main-content-packege-manage">
        <header class="d-flex justify-content-between align-items-center page-header">
            <div>
                <h1>{{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</h1>
                <p class="breadcrumb-custom">Package Management >
                    {{ isset($package) ? 'Edit Regular Package' : 'Add Regular Package' }}</p>
            </div>
            <div>
                <button id="submitBtn" class="btn btn-save jatio-bg-color">
                    {{ isset($package) ? 'Update Package' : 'Save Package' }}
                </button>
            </div>
        </header>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi  bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi  bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi  bi-exclamation-triangle"></i> Please correct the following errors:
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title">Package Details</h5>

                <form id="packageForm" method="POST"
                    action="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : route('admin.regular-packege-management.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @if (isset($package))
                        @method('PUT')
                    @endif

                    <div class="row g-4">
                        <!-- Multiple Image Uploader -->
                        <div class="col-lg-12 package-multiple-image-upload-form">
                            <label for="multiple-image-upload" class="form-label">Upload Images</label>

                            <div id="multiple-image-upload" data-model-type="App\Models\Package"
                                data-model-id="{{ $package->id ?? '' }}"
                                data-upload-url="{{ route('admin.regular-packege-management.store') }}"
                                data-update-url="{{ isset($package) ? route('admin.regular-packege-management.update', $package) : '' }}"
                                data-images-url="{{ route('admin.images.get') }}"
                                data-primary-url="{{ url('admin/images') }}/:id/primary"
                                data-reorder-url="{{ route('admin.images.reorder') }}"
                                data-alt-text-url="{{ url('admin/images') }}/:id/alt-text"
                                data-delete-url="{{ url('admin/images') }}/:id"
                                data-existing-images="{{ isset($package) ? $package->images->toJson() : '[]' }}"
                                data-max-files="12" data-max-file-size="{{ 5 * 1024 * 1024 }}">
                                <!-- JS will render the uploader UI here -->
                            </div>

                            <!-- Real file input that will be populated before submit -->
                            <input type="file" id="package_images_input" name="images[]" multiple accept="image/*"
                                style="display:none;">
                            <!-- Gallery images (IDs) from server gallery selection -->
                            <input type="hidden" id="gallery_images_input" name="gallery_images" value="">
                        </div>

                        <!-- Package fields -->
                        <div class="col-lg-12">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="packageName" class="form-label">Package Name</label>
                                    <input type="text" class="form-control @error('packageName') is-invalid @enderror"
                                        id="packageName" name="packageName"
                                        value="{{ old('packageName', $package->name ?? 'Kids Fun') }}" required>
                                    @error('packageName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="subTitle" class="form-label">Sub Title</label>
                                    <input type="text" class="form-control @error('subTitle') is-invalid @enderror"
                                        id="subTitle" name="subTitle"
                                        value="{{ old('subTitle', $package->subtitle ?? 'Kids Friendly Fun Zone') }}">
                                    @error('subTitle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="packageType" class="form-label">Package Type</label>
                                    <select class="form-select @error('packageType') is-invalid @enderror" id="packageType"
                                        name="packageType" required>
                                        <option value="">Select Package Type</option>
                                        <option value="Single"
                                            {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Single' ? 'selected' : '' }}>
                                            Single</option>
                                        <option value="Bundle"
                                            {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Bundle' ? 'selected' : '' }}>
                                            Bundle</option>
                                        <option value="Group"
                                            {{ old('packageType', optional($package?->variants->first())->variant_name) == 'Group' ? 'selected' : '' }}>
                                            Group</option>
                                    </select>
                                    @error('packageType')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="details" class="form-label">Details</label>
                                    <textarea class="form-control @error('details') is-invalid @enderror" id="details" name="details" rows="4"
                                        placeholder="Type here...">{{ old('details', $package->details ?? '') }}</textarea>
                                    @error('details')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="displayStartingPrice" class="form-label">Display Starting Price
                                        (TK)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">৳</span>
                                        <input type="number" step="0.01"
                                            class="form-control @error('displayStartingPrice') is-invalid @enderror"
                                            id="displayStartingPrice" name="displayStartingPrice"
                                            value="{{ old('displayStartingPrice', $package->display_starting_price ?? '') }}"
                                            placeholder="e.g., 99.00">
                                    </div>
                                    <small class="form-text text-muted">This price will be displayed as "Starting from TK
                                        X". Leave empty to use calculated min price.</small>
                                    @error('displayStartingPrice')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="minParticipant" class="form-label">Minimum Participant</label>
                                    <input type="number"
                                        class="form-control @error('minParticipant') is-invalid @enderror"
                                        id="minParticipant" name="minParticipant"
                                        value="{{ old('minParticipant', $package->min_participants ?? 5) }}" required>
                                    @error('minParticipant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="maxParticipant" class="form-label">Maximum Participant</label>
                                    <input type="number"
                                        class="form-control @error('maxParticipant') is-invalid @enderror"
                                        id="maxParticipant" name="maxParticipant"
                                        value="{{ old('maxParticipant', $package->max_participants ?? 50) }}" required>
                                    @error('maxParticipant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing card -->
                    <div class="card mt-4">
                        <div class="card-body p-4">
                            <h5 class="card-title">Pricing Details</h5>

                            <div class="mb-4">
                                <label class="form-label">Weekdays Prices</label>
                                <ul class="nav nav-pills pricing-pills mb-3" id="weekdayPills">
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="sunday"
                                            data-price="{{ old('sundayPrice', '1000') }}">Sunday</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="monday"
                                            data-price="{{ old('mondayPrice', optional($package?->variants->first()?->prices->where('price_type', 'weekday')->first())->amount ?? '1000') }}">Monday</a>
                                    </li>
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="tuesday"
                                            data-price="{{ old('tuesdayPrice', '1000') }}">Tuesday</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="wednesday"
                                            data-price="{{ old('wednesdayPrice', '1000') }}">Wednesday</a></li>
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="thursday"
                                            data-price="{{ old('thursdayPrice', '1000') }}">Thursday</a></li>
                                </ul>

                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2">Selected Weekday:</strong>
                                    <span id="selectedWeekday">Monday</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="weekdayPrice" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01"
                                                class="form-control @error('weekdayPrice') is-invalid @enderror"
                                                id="weekdayPrice" name="weekdayPrice"
                                                value="{{ old('weekdayPrice', optional($package?->variants->first()?->prices->where('price_type', 'weekday')->first())->amount ?? 1000) }}"
                                                required>
                                        </div>
                                        @error('weekdayPrice')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Weekend Prices</label>
                                <ul class="nav nav-pills pricing-pills mb-3" id="weekendPills">
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="friday"
                                            data-price="{{ old('fridayPrice', optional($package?->variants->first()?->prices->where('price_type', 'weekend')->first())->amount ?? 1500) }}">Friday</a>
                                    </li>
                                    <li class="nav-item"><a href="#" class="nav-link" data-day="saturday"
                                            data-price="{{ old('saturdayPrice', '1500') }}">Saturday</a></li>
                                </ul>

                                <div class="d-flex align-items-center mb-2">
                                    <strong class="me-2">Selected Weekend:</strong>
                                    <span id="selectedWeekend">Friday</span>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="weekendPrice" class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" step="0.01"
                                                class="form-control @error('weekendPrice') is-invalid @enderror"
                                                id="weekendPrice" name="weekendPrice"
                                                value="{{ old('weekendPrice', optional($package?->variants->first()?->prices->where('price_type', 'weekend')->first())->amount ?? 1500) }}"
                                                required>
                                        </div>
                                        @error('weekendPrice')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- hidden fields kept minimal -->
                    <input type="hidden" id="selected_weekday" name="selected_weekday" value="monday">
                    <input type="hidden" id="selected_weekend" name="selected_weekend" value="friday">

                </form>
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize uploader instance for the container
            const container = document.getElementById('multiple-image-upload');
            if (!container) return;

            // instantiate your class (assumes the class is provided by admin/js/multiple-image-upload.js)
            const uploader = new MultipleImageUpload(container.id, {
                maxFiles: parseInt(container.dataset.maxFiles || container.dataset.maxFiles) || 12,
                maxFileSize: parseInt(container.dataset.maxFileSize || (5 * 1024 * 1024)) || (5 * 1024 *
                    1024),
            });

            // keep a global reference
            window.multipleImageUploadInstance = uploader;

            // Pricing pills logic (single source of truth)
            const weekdayPills = document.querySelectorAll('#weekdayPills .nav-link');
            const weekendPills = document.querySelectorAll('#weekendPills .nav-link');
            const selectedWeekdayLabel = document.getElementById('selectedWeekday');
            const selectedWeekendLabel = document.getElementById('selectedWeekend');
            const selectedWeekdayInput = document.getElementById('selected_weekday');
            const selectedWeekendInput = document.getElementById('selected_weekend');
            const weekdayPriceInput = document.getElementById('weekdayPrice');
            const weekendPriceInput = document.getElementById('weekendPrice');

            // helper to set active pill group
            function activatePillGroup(pills, clicked) {
                pills.forEach(p => p.classList.remove('active'));
                clicked.classList.add('active');
            }

            // initialize pills — make default active (use dataset or fallback)
            (function initPills() {
                const prevWeekday = "{{ old('selected_weekday', 'monday') }}";
                const prevWeekend = "{{ old('selected_weekend', 'friday') }}";

                // weekday
                let found = false;
                weekdayPills.forEach(p => {
                    if (p.dataset.day === prevWeekday) {
                        activatePillGroup(weekdayPills, p);
                        selectedWeekdayLabel.textContent = p.textContent.trim();
                        selectedWeekdayInput.value = p.dataset.day;
                        weekdayPriceInput.value = p.dataset.price || weekdayPriceInput.value;
                        found = true;
                    }
                });
                if (!found && weekdayPills.length) {
                    activatePillGroup(weekdayPills, weekdayPills[0]);
                    selectedWeekdayLabel.textContent = weekdayPills[0].textContent.trim();
                    selectedWeekdayInput.value = weekdayPills[0].dataset.day;
                    weekdayPriceInput.value = weekdayPills[0].dataset.price || weekdayPriceInput.value;
                }

                // weekend
                found = false;
                weekendPills.forEach(p => {
                    if (p.dataset.day === prevWeekend) {
                        activatePillGroup(weekendPills, p);
                        selectedWeekendLabel.textContent = p.textContent.trim();
                        selectedWeekendInput.value = p.dataset.day;
                        weekendPriceInput.value = p.dataset.price || weekendPriceInput.value;
                        found = true;
                    }
                });
                if (!found && weekendPills.length) {
                    activatePillGroup(weekendPills, weekendPills[0]);
                    selectedWeekendLabel.textContent = weekendPills[0].textContent.trim();
                    selectedWeekendInput.value = weekendPills[0].dataset.day;
                    weekendPriceInput.value = weekendPills[0].dataset.price || weekendPriceInput.value;
                }
            })();

            // click handlers
            weekdayPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    activatePillGroup(weekdayPills, this);
                    selectedWeekdayLabel.textContent = this.textContent.trim();
                    selectedWeekdayInput.value = this.dataset.day;
                    if (this.dataset.price) weekdayPriceInput.value = this.dataset.price;
                });
            });

            weekendPills.forEach(pill => {
                pill.addEventListener('click', function(e) {
                    e.preventDefault();
                    activatePillGroup(weekendPills, this);
                    selectedWeekendLabel.textContent = this.textContent.trim();
                    selectedWeekendInput.value = this.dataset.day;
                    if (this.dataset.price) weekendPriceInput.value = this.dataset.price;
                });
            });

            // sync price inputs to currently active pill's price dataset (live update)
            weekdayPriceInput.addEventListener('input', function() {
                const active = document.querySelector('#weekdayPills .nav-link.active');
                if (active) active.dataset.price = this.value;
            });
            weekendPriceInput.addEventListener('input', function() {
                const active = document.querySelector('#weekendPills .nav-link.active');
                if (active) active.dataset.price = this.value;
            });

            // form submit: attach selected files from uploader into real input
            const form = document.getElementById('packageForm');
            const submitBtn = document.getElementById('submitBtn');
            const realFileInput = document.getElementById('package_images_input');
            const galleryInput = document.getElementById('gallery_images_input');

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // basic client validation
                if (!validateFormClient()) return;

                // Gather files from uploader instance
                let selectedFiles = [];
                if (window.multipleImageUploadInstance && typeof window.multipleImageUploadInstance
                    .getSelectedFiles === 'function') {
                    selectedFiles = window.multipleImageUploadInstance.getSelectedFiles() || [];
                }

                // Separate real File objects and gallery images
                const realFiles = selectedFiles.filter(f => f instanceof File);
                const galleryImages = selectedFiles.filter(f => f.isGalleryImage);

                // Attach real files to the hidden file input using DataTransfer
                if (realFiles.length > 0) {
                    const dt = new DataTransfer();
                    realFiles.forEach(f => dt.items.add(f));
                    realFileInput.files = dt.files;
                } else {
                    // clear file input
                    realFileInput.value = '';
                }

                // Attach gallery image IDs as JSON string (if your backend supports)
                if (galleryImages.length > 0) {
                    const ids = galleryImages.map(g => g.galleryId || g.id).filter(Boolean);
                    galleryInput.value = JSON.stringify(ids);
                } else {
                    galleryInput.value = '';
                }

                // Finally submit the form
                form.submit();
            });

            // simple client-side validation (expand as needed)
            function validateFormClient() {
                const required = document.querySelectorAll('#packageForm [required]');
                for (let el of required) {
                    if (!el.value || el.value.toString().trim() === '') {
                        el.classList.add('is-invalid');
                        el.focus();
                        return false;
                    } else {
                        el.classList.remove('is-invalid');
                    }
                }

                // prices validation
                if (parseFloat(weekdayPriceInput.value) < 0 || parseFloat(weekendPriceInput.value) < 0) {
                    alert('Prices must be >= 0');
                    return false;
                }

                return true;
            }

            // When gallery modal selects images it should call MultipleImageUpload.handleGallerySelection
            // and the uploader will add gallery images to its internal files array as objects with isGalleryImage = true
        });
    </script>
@endpush
