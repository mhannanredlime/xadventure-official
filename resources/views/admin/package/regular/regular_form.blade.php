@csrf
@if (isset($package))
    @method('PUT')
@endif

{{-- ---------- Image Upload Section ---------- --}}
<div class="card p-4 mb-4">
    <h5 class="card-title"><i class="bi bi-images me-2"></i>Package Images</h5>

    <div class="row" x-data="{
        images: [],
        init() {
            // Reset input on load
            this.$refs.fileInput.value = '';
        },
        handleFileChange(event) {
            const selectedFiles = Array.from(event.target.files);
            // Append new files to existing ones
            selectedFiles.forEach(file => {
                if (file.type.startsWith('image/')) {
                    // Check for duplicate names to avoid confusion (optional, but good)
                    if (!this.images.some(img => img.file.name === file.name && img.file.size === file.size)) {
                        this.images.push({
                            id: Date.now() + Math.random().toString(36),
                            file: file,
                            preview: URL.createObjectURL(file)
                        });
                    }
                }
            });
            this.updateFileInput();
        },
        removeImage(index) {
            URL.revokeObjectURL(this.images[index].preview);
            this.images.splice(index, 1);
            this.updateFileInput();
        },
        updateFileInput() {
            const dataTransfer = new DataTransfer();
            this.images.forEach(img => {
                dataTransfer.items.add(img.file);
            });
            this.$refs.fileInput.files = dataTransfer.files;
        }
    }">
        <div class="col-12">
            <label class="form-label">Upload Package Images (Max 4 images)</label>

            <div class="mb-3">
                <button type="button" class="btn  btn-save" @click="$refs.fileInput.click()">
                    <i class="bi bi-cloud-upload me-1"></i> Choose Images
                </button>
                <input type="file" x-ref="fileInput" name="images[]" multiple accept="image/*" class="d-none"
                    @change="handleFileChange($event)">
                <span class="text-muted ms-2" x-show="images.length > 0"
                    x-text="images.length + ' new image(s) selected'"></span>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-3">
                {{-- Existing Images --}}
                @if (isset($package) && $package->images)
                    @foreach ($package->images as $key => $image)
                        <div class="position-relative" style="width: 150px; height: 150px;">
                            <img src="{{ asset('storage/' . $image->image_path) }}"
                                class="w-100 h-100 object-fit-cover rounded border" alt="Package Image">
                            <span class="position-absolute top-0 end-0 badge bg-secondary m-1">Existing</span>
                            @if ($key === 0 && (!old('images') && !request()->has('images')))
                                {{-- Show Main Tag on existing if it's the first one --}}
                                <span class="position-absolute bottom-0 start-0 badge jatio-bg-color m-1">Main
                                    Image</span>
                            @endif
                        </div>
                    @endforeach
                @endif

                {{-- New Upload Previews --}}
                <template x-for="(img, index) in images" :key="img.id">
                    <div class="position-relative" style="width: 150px; height: 150px;">
                        <img :src="img.preview" class="w-100 h-100 object-fit-cover rounded border" alt="Preview">

                        {{-- Remove Button --}}
                        <button type="button"
                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center"
                            style="width: 24px; height: 24px;" @click="removeImage(index)">
                            <i class="bi bi-x"></i>
                        </button>

                        {{-- Main Image Badge (First in the NEW list, if no existing? OR just first in this list) --}}
                        {{-- User req: "First uploaded image should be marked" --}}
                        {{-- We assume this means index 0 of the new batch --}}
                        <template x-if="index === 0">
                            <span class="position-absolute bottom-0 start-0 badge jatio-bg-color m-1">Main Image</span>
                        </template>
                    </div>
                </template>
            </div>

            <small class="text-muted mt-2 d-block">
                Selected images will be uploaded when you save the package.
            </small>
        </div>
    </div>
</div>

{{-- Package Details --}}
<div class="card p-4 mb-4">
    <h5 class="card-title">Package Details</h5>
    <div class="row g-4">
        <div class="col-md-4">
            <label class="form-label">Package Type <span class="text-danger">*</span></label>
            {{-- @dd($packageTypes   ); --}}
            <select class="form-select @error('packageType') is-invalid @enderror" name="packageType" required>
                <option value="">Select Package Type</option>
                @foreach ($packageTypes as $type)
                    <option value="{{ $type->id }}"
                        {{ old('packageType', @$package->package_type_id) == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('packageType')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Package Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('packageName') is-invalid @enderror" name="packageName"
                value="{{ old('packageName', @$package->name) }}" placeholder="Enter package name" required>
            @error('packageName')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Sub Title</label>
            <input type="text" class="form-control @error('subTitle') is-invalid @enderror" name="subTitle"
                value="{{ old('subTitle', @$package->subtitle) }}" placeholder="Enter subtitle">
            @error('subTitle')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>



        <div class="col-12">
            <label class="form-label">Package Details</label>
            <textarea class="form-control @error('details') is-invalid @enderror" name="details" rows="5"
                placeholder="Describe the package details...">{{ old('details', @$package->details) }}</textarea>
            @error('details')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Display Base Price (৳) <span class="text-danger">*</span></label>
            <input type="number" step="0.01"
                class="form-control @error('displayStartingPrice') is-invalid @enderror" name="displayStartingPrice"
                value="{{ old('displayStartingPrice', @$package->display_starting_price) }}" min="50"
                placeholder="0.00" required>
            @error('displayStartingPrice')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Minimum Participants <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('minParticipant') is-invalid @enderror"
                name="minParticipant" value="{{ old('minParticipant', @$package->min_participants) }}" min="1"
                required>
            @error('minParticipant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4">
            <label class="form-label">Maximum Participants <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('maxParticipant') is-invalid @enderror"
                name="maxParticipant" value="{{ old('maxParticipant', @$package->max_participants) }}"
                min="1" required>
            @error('maxParticipant')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Package Pricing use direct html form  --}}
@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('packagePricing', (initialPrices) => ({
                prices: initialPrices,
                applyAllPrice: '',
                days: ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'],

                init() {
                    // Ensure all days exist
                    this.days.forEach(day => {
                        if (this.prices[day] === undefined) {
                            this.prices[day] = '';
                        }
                    });
                },

                applyToAll() {
                    if (this.applyAllPrice) {
                        this.days.forEach(day => {
                            this.prices[day] = this.applyAllPrice;
                        });
                    }
                },

                removeDay(day) {
                    this.prices[day] = '';
                },

                get formattedPrices() {
                    return JSON.stringify(this.days.map(day => ({
                        day: day,
                        price: this.prices[day]
                    })));
                },

                isWeekend(day) {
                    return ['fri', 'sat'].includes(day);
                }
            }));
        });
    </script>
@endpush

{{-- Package Pricing use direct html form  --}}
@php
    $initialPrices = [];
    if (isset($package) && $package->packagePrices) {
        foreach ($package->packagePrices as $price) {
            $initialPrices[$price->day] = $price->price;
        }
    }
@endphp

<div class="card p-4 mb-4" x-data="packagePricing(@json($initialPrices))">
    <h5 class="card-title"><i class="bi bi-tag me-2"></i>Package Pricing (Day-wise)</h5>

    <div class="mb-4">
        <label class="form-label">Apply Same Price to All Days:</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" class="form-control" placeholder="Enter price for all days" min="0"
                step="0.01" x-model="applyAllPrice" @input="applyToAll">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>Day</th>
                    <th>Price (৳)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="day in days" :key="day">
                    <tr :class="isWeekend(day) ? 'table-warning' : ''">
                        <td class="fw-bold text-uppercase">
                            <span x-text="day"></span>
                            <template x-if="isWeekend(day)">
                                <span class="badge bg-warning text-dark ms-2 weekend-badge">Weekend</span>
                            </template>
                        </td>
                        <td>
                            <input type="number" class="form-control text-center" x-model="prices[day]"
                                placeholder="100" maxlength="8" min="0" step="0.01">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" @click="removeDay(day)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="day_prices" :value="formattedPrices">
</div>

<div class="d-flex justify-content-end mt-4">
    <x-admin.button type="submit" id="submitBtn" color="save">
        {{ isset($package) ? 'Update Package' : 'Save Package' }}
    </x-admin.button>
</div>
