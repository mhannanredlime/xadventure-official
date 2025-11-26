@extends('layouts.admin')

@section('title', 'Add ATV/UTV Package')

@section('content')
     <main class="content-area">
    <header class="content-header d-flex justify-content-between align-items-center">
      <div>
        <h1 class="h4 fw-bold mb-1">{{ isset($package) ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package' }}</h1>
        <p class="breadcrumb-custom">Package Management > <strong>{{ isset($package) ? 'Edit ATV/UTV Package' : 'Add ATV/UTV Package' }}</strong></p>
      </div>
      @if(isset($package))
        <button class="btn save-package-btn jatio-bg-color" onclick="updateFormData(); document.getElementById('updateForm').submit()">Update Package</button>
      @else
        <button class="btn save-package-btn jatio-bg-color" onclick="updateFormData(); document.getElementById('createForm').submit()">Save Package</button>
      @endif
    </header>

    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi  bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi  bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi  bi-exclamation-triangle"></i> Please correct the following errors:
        <ul class="mb-0 mt-2">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    <div class="card mt-4">
      <div class="card-body p-4">
        <h3 class="card-header-title">Package Details</h3>
        <div class="row g-4">
          <div class="col-lg-6">
            <label class="form-label">Vehicle Type Images</label>
            <div id="vehicle-type-images" class="vehicle-type-images-container">
              <div class="text-center text-muted py-4">
                <i class="bi  bi-image fa-3x mb-3"></i>
                <p>Select a vehicle type to see its images</p>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="row g-4">
              <div class="col-md-6">
                <label for="vehicleType" class="form-label">Vehicle Type</label>
                <select class="form-select @error('vehicleType') is-invalid @enderror" id="vehicleType" name="vehicleType" required>
                  <option value="">Select Vehicle Type</option>
                  @foreach($vehicleTypes as $vehicleType)
                    @php
                      $imagesJson = $vehicleType->images->toJson();
                      $displayImage = $vehicleType->display_image_url;
                    @endphp
                    <option value="{{ $vehicleType->name }}"
                            data-images="{{ $imagesJson }}"
                            data-display-image="{{ $displayImage }}"
                            {{ old('vehicleType', (isset($package) && $package->type === strtolower($vehicleType->name)) ? $vehicleType->name : '') == $vehicleType->name ? 'selected' : '' }}>
                      {{ $vehicleType->name }}
                    </option>
                  @endforeach
                </select>
                @error('vehicleType')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="col-md-6">
                <label for="packageName" class="form-label">Package Name</label>
                <input type="text" class="form-control @error('packageName') is-invalid @enderror" id="packageName" name="packageName" value="{{ old('packageName', $package->name ?? 'ATV/UTV Trail Rides') }}" required>
                @error('packageName')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
                             <div class="col-12">
                 <label for="subTitle" class="form-label">Sub Title</label>
                 <input type="text" class="form-control @error('subTitle') is-invalid @enderror" id="subTitle" name="subTitle" value="{{ old('subTitle', $package->subtitle ?? 'Kids Fun Zone') }}">
                 @error('subTitle')
                   <div class="invalid-feedback">{{ $message }}</div>
                 @enderror
               </div>
                             <div class="col-12">
                 <label for="notes" class="form-label">Notes</label>
                 <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4" placeholder="Type here...">{{ old('notes', $package->notes ?? '') }}</textarea>
                 @error('notes')
                   <div class="invalid-feedback">{{ $message }}</div>
                 @enderror
               </div>

            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-body p-4">
        <h3 class="card-header-title">Pricing Details</h3>
                 <div class="mb-4">
           <h4 class="h6 fw-bold mb-3">Weekdays Prices</h4>
       <div class="day-selector mb-3" id="weekdaySelector">
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Sunday</button>
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Monday</button>
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Tuesday</button>
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Wednesday</button>
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Thursday</button>
            </div>
           <div class="row g-4">
             <div class="col-md-6">
               <label for="weekdaySingle" class="form-label">Single Rider</label>
               <div class="input-group">
                 <span class="input-group-text">৳</span>
                 <input type="text" class="form-control @error('weekdaySingle') is-invalid @enderror" id="weekdaySingle" name="weekdaySingle" value="{{ old('weekdaySingle', isset($package) && $package->variants->where('variant_name', 'Single Rider')->first() ? $package->variants->where('variant_name', 'Single Rider')->first()->prices->where('price_type', 'weekday')->first()->amount ?? 1000 : 1000) }}" required>
               </div>
               @error('weekdaySingle')
                 <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
             <div class="col-md-6">
               <label for="weekdayDouble" class="form-label">Double Rider</label>
               <div class="input-group">
                 <span class="input-group-text">৳</span>
                 <input type="text" class="form-control @error('weekdayDouble') is-invalid @enderror" id="weekdayDouble" name="weekdayDouble" value="{{ old('weekdayDouble', isset($package) && $package->variants->where('variant_name', 'Double Rider')->first() ? $package->variants->where('variant_name', 'Double Rider')->first()->prices->where('price_type', 'weekday')->first()->amount ?? 1000 : 1000) }}" required>
               </div>
               @error('weekdayDouble')
                 <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
           </div>
         </div>
                 <div>
           <h4 class="h6 fw-bold mb-3">Weekend Prices</h4>
           <div class="day-selector mb-3" id="weekendSelector">
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Friday</button>
              <button type="button" class="btn disabled" disabled style="opacity: 0.6; cursor: not-allowed;">Saturday</button>
            </div>
           <div class="row g-4">
             <div class="col-md-6">
               <label for="weekendSingle" class="form-label">Single Rider</label>
               <div class="input-group">
                 <span class="input-group-text">৳</span>
                 <input type="text" class="form-control @error('weekendSingle') is-invalid @enderror" id="weekendSingle" name="weekendSingle" value="{{ old('weekendSingle', isset($package) && $package->variants->where('variant_name', 'Single Rider')->first() ? $package->variants->where('variant_name', 'Single Rider')->first()->prices->where('price_type', 'weekend')->first()->amount ?? 1000 : 1000) }}" required>
               </div>
               @error('weekendSingle')
                 <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
             <div class="col-md-6">
               <label for="weekendDouble" class="form-label">Double Rider</label>
               <div class="input-group">
                 <span class="input-group-text">৳</span>
                 <input type="text" class="form-control @error('weekendDouble') is-invalid @enderror" id="weekendDouble" name="weekendDouble" value="{{ old('weekendDouble', isset($package) && $package->variants->where('variant_name', 'Double Rider')->first() ? $package->variants->where('variant_name', 'Double Rider')->first()->prices->where('price_type', 'weekend')->first()->amount ?? 1000 : 1000) }}" required>
               </div>
               @error('weekendDouble')
                 <div class="invalid-feedback">{{ $message }}</div>
               @enderror
             </div>
           </div>
         </div>
      </div>
    </div>
  </main>

  @if(isset($package))
    <form id="updateForm" method="POST" action="{{ route('admin.atvutv-packege-management.update', $package) }}" enctype="multipart/form-data" style="display: none;">
      @csrf
      @method('PUT')
      <input type="hidden" name="vehicleType" value="">
      <input type="hidden" name="packageName" value="">
      <input type="hidden" name="subTitle" value="">
      <input type="hidden" name="notes" value="">

      <input type="hidden" name="weekdaySingle" value="">
      <input type="hidden" name="weekdayDouble" value="">
      <input type="hidden" name="weekendSingle" value="">
      <input type="hidden" name="weekendDouble" value="">
      <input type="hidden" name="selected_weekday" value="monday">
      <input type="hidden" name="selected_weekend" value="friday">
      <input type="file" name="images[]" multiple accept="image/*" style="display: none;">
    </form>
  @else
    <form id="createForm" method="POST" action="{{ route('admin.atvutv-packege-management.store') }}" enctype="multipart/form-data" style="display: none;">
      @csrf
      <input type="hidden" name="vehicleType" value="">
      <input type="hidden" name="packageName" value="">
      <input type="hidden" name="subTitle" value="">
      <input type="hidden" name="notes" value="">

      <input type="hidden" name="weekdaySingle" value="">
      <input type="hidden" name="weekdayDouble" value="">
      <input type="hidden" name="weekendSingle" value="">
      <input type="hidden" name="weekendDouble" value="">
      <input type="hidden" name="selected_weekday" value="monday">
      <input type="hidden" name="selected_weekend" value="friday">
      <input type="file" name="images[]" multiple accept="image/*" style="display: none;">
    </form>
  @endif

  <script>
    // Vehicle Type Image Display Functions
    function initializeVehicleTypeImages() {
      const vehicleTypeSelect = document.getElementById('vehicleType');
      if (vehicleTypeSelect.value) {
        updateVehicleTypeImages();
      }
    }

    function updateVehicleTypeImages() {
      const vehicleTypeSelect = document.getElementById('vehicleType');
      const imageContainer = document.getElementById('vehicle-type-images');
      const selectedOption = vehicleTypeSelect.options[vehicleTypeSelect.selectedIndex];

      if (!selectedOption || !selectedOption.value) {
        imageContainer.innerHTML = `
          <div class="text-center text-muted py-4">
            <i class="bi  bi-image fa-3x mb-3"></i>
            <p>Select a vehicle type to see its images</p>
          </div>
        `;
        return;
      }

      try {
        const images = JSON.parse(selectedOption.dataset.images || '[]');
        const displayImage = selectedOption.dataset.displayImage;

        if (images.length === 0 && !displayImage) {
          imageContainer.innerHTML = `
            <div class="text-center text-muted py-4">
              <i class="bi  bi-image fa-3x mb-3"></i>
              <p>No images available for ${selectedOption.text}</p>
            </div>
          `;
          return;
        }

        let imageHtml = '<div class="vehicle-type-images-grid">';

        if (images.length > 0) {
          images.forEach((image, index) => {
            const isPrimary = image.is_primary ? 'border-primary' : 'border-secondary';
            imageHtml += `
              <div class="vehicle-type-image-item ${isPrimary}">
                <img src="${image.url}" alt="${image.alt_text || selectedOption.text}"
                     class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
                ${image.is_primary ? '<span class="badge bg-primary position-absolute top-0 end-0 m-1">Primary</span>' : ''}
              </div>
            `;
          });
        } else if (displayImage) {
          imageHtml += `
            <div class="vehicle-type-image-item border-primary">
              <img src="${displayImage}" alt="${selectedOption.text}"
                   class="img-fluid rounded" style="max-height: 200px; width: 100%; object-fit: cover;">
            </div>
          `;
        }

        imageHtml += '</div>';
        imageContainer.innerHTML = imageHtml;

      } catch (error) {
        // Error parsing vehicle type images
        imageContainer.innerHTML = `
          <div class="text-center text-muted py-4">
            <i class="bi  bi-exclamation-triangle fa-3x mb-3"></i>
            <p>Error loading images for ${selectedOption.text}</p>
          </div>
        `;
      }
    }

    function previewImage(input) {
       if (input.files && input.files[0]) {
         const reader = new FileReader();
         reader.onload = function(e) {
           const imageElement = document.getElementById('packageImage');
           if (imageElement) {
             // Check if it's currently a div (no image selected)
             if (imageElement.tagName === 'DIV') {
               // Create a new img element
               const newImg = document.createElement('img');
               newImg.id = 'packageImage';
               newImg.style.height = '100%';
               newImg.style.width = '100%';
               newImg.style.objectFit = 'cover';
               newImg.src = e.target.result;
               newImg.alt = 'Package Image';

               // Replace the div with the new img
               imageElement.parentNode.replaceChild(newImg, imageElement);
             } else {
               // It's already an img element, just update src
               imageElement.src = e.target.result;
             }
           }
         };
         reader.readAsDataURL(input.files[0]);
       }
     }

    // Global variables for form data
    let selectedDays = { weekday: 'monday', weekend: 'friday' };

    function updateFormData() {
      // Ensure selectedDays reflects hidden inputs if present
      const hiddenWeekday = document.querySelector('input[name="selected_weekday"]');
      const hiddenWeekend = document.querySelector('input[name="selected_weekend"]');
      if (hiddenWeekday && hiddenWeekday.value) selectedDays.weekday = hiddenWeekday.value;
      if (hiddenWeekend && hiddenWeekend.value) selectedDays.weekend = hiddenWeekend.value;
      const form = document.getElementById('{{ isset($package) ? "updateForm" : "createForm" }}');

      // Get form field values
      const vehicleType = document.getElementById('vehicleType').value;
      const packageName = document.getElementById('packageName').value;
      const subTitle = document.getElementById('subTitle').value;
      const notes = document.getElementById('notes').value;

      const weekdaySingle = document.getElementById('weekdaySingle').value;
      const weekdayDouble = document.getElementById('weekdayDouble').value;
      const weekendSingle = document.getElementById('weekendSingle').value;
      const weekendDouble = document.getElementById('weekendDouble').value;

      // ATV/UTV Form values logged

      // Update hidden form fields
      form.querySelector('input[name="vehicleType"]').value = vehicleType;
      form.querySelector('input[name="packageName"]').value = packageName;
      form.querySelector('input[name="subTitle"]').value = subTitle;
      form.querySelector('input[name="notes"]').value = notes;

      form.querySelector('input[name="weekdaySingle"]').value = weekdaySingle;
      form.querySelector('input[name="weekdayDouble"]').value = weekdayDouble;
      form.querySelector('input[name="weekendSingle"]').value = weekendSingle;
      form.querySelector('input[name="weekendDouble"]').value = weekendDouble;

      // Update selected day states
      form.querySelector('input[name="selected_weekday"]').value = selectedDays.weekday;
      form.querySelector('input[name="selected_weekend"]').value = selectedDays.weekend;

       // Handle multiple image upload
       if (window.multipleImageUploadInstance) {
         const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
         const formImageInput = form.querySelector('input[name="images[]"]');
         if (selectedFiles.length > 0) {
           // Create a new FileList-like object
           const dt = new DataTransfer();
           selectedFiles.forEach(file => dt.items.add(file));
           formImageInput.files = dt.files;
         }
       }
     }

    // Update form data before submission
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize vehicle type image display
      initializeVehicleTypeImages();

      // Handle vehicle type selection change
      document.getElementById('vehicleType').addEventListener('change', function() {
        updateVehicleTypeImages();
      });

      // Handle weekday selector clicks
       const weekdayButtons = document.querySelectorAll('#weekdaySelector .btn');
       weekdayButtons.forEach(button => {
         button.addEventListener('click', function(e) {
           e.preventDefault();

           // Save current prices to the previously active button
           const previouslyActive = document.querySelector('#weekdaySelector .btn.active');
           if (previouslyActive) {
             const currentSingle = document.getElementById('weekdaySingle').value;
             const currentDouble = document.getElementById('weekdayDouble').value;
             previouslyActive.setAttribute('data-single', currentSingle);
             previouslyActive.setAttribute('data-double', currentDouble);
           }

           // Remove active class from all weekday buttons
           weekdayButtons.forEach(b => b.classList.remove('active'));
           // Add active class to clicked button
           this.classList.add('active');

           // Update the label and prices
           const selectedDay = this.getAttribute('data-day');
           const singlePrice = this.getAttribute('data-single');
           const doublePrice = this.getAttribute('data-double');

           document.getElementById('selectedWeekday').textContent = this.textContent;
           document.getElementById('weekdaySingle').value = singlePrice;
           document.getElementById('weekdayDouble').value = doublePrice;

           // Save the selected weekday state
           selectedDays.weekday = selectedDay;

           // Switched to weekday
         });
       });

             // Handle weekend selector clicks
       const weekendButtons = document.querySelectorAll('#weekendSelector .btn');
       weekendButtons.forEach(button => {
         button.addEventListener('click', function(e) {
           e.preventDefault();

           // Save current prices to the previously active button
           const previouslyActive = document.querySelector('#weekendSelector .btn.active');
           if (previouslyActive) {
             const currentSingle = document.getElementById('weekendSingle').value;
             const currentDouble = document.getElementById('weekendDouble').value;
             previouslyActive.setAttribute('data-single', currentSingle);
             previouslyActive.setAttribute('data-double', currentDouble);
           }

           // Remove active class from all weekend buttons
           weekendButtons.forEach(b => b.classList.remove('active'));
           // Add active class to clicked button
           this.classList.add('active');

           // Update the label and prices
           const selectedDay = this.getAttribute('data-day');
           const singlePrice = this.getAttribute('data-single');
           const doublePrice = this.getAttribute('data-double');

           document.getElementById('selectedWeekend').textContent = this.textContent;
           document.getElementById('weekendSingle').value = singlePrice;
           document.getElementById('weekendDouble').value = doublePrice;

           // Save the selected weekend day state
           selectedDays.weekend = selectedDay;

           // Switched to weekend
         });
       });

      // Handle vehicle type changes
      const vehicleTypeSelect = document.getElementById('vehicleType');
      if (vehicleTypeSelect) {
        vehicleTypeSelect.addEventListener('change', function() {
          const selectedType = this.value;
          // Vehicle type changed

          // Update package name based on vehicle type
          const packageNameInput = document.getElementById('packageName');
          if (packageNameInput && !packageNameInput.value && selectedType) {
            // Generate package name based on vehicle type
            const packageName = selectedType + ' Adventure Package';
            packageNameInput.value = packageName;
          }
        });
      }

      // Handle form submission
      const submitButtons = document.querySelectorAll('.save-package-btn');
      submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();

          // Validate form before submission
          if (validateForm()) {
            updateFormData();
            const form = document.getElementById('{{ isset($package) ? "updateForm" : "createForm" }}');
            form.submit();
          }
        });
      });

      // Add real-time validation
      const inputs = document.querySelectorAll('input[required], select[required]');
      inputs.forEach(input => {
        input.addEventListener('blur', function() {
          validateField(this);
        });

        input.addEventListener('input', function() {
          clearFieldError(this);
        });
      });

             // Add hover effect for image upload
       const imageContainer = document.querySelector('.package-image-container');
       const overlay = document.querySelector('.image-upload-overlay');

       if (imageContainer && overlay) {
         imageContainer.addEventListener('mouseenter', function() {
           overlay.style.opacity = '1';
         });

         imageContainer.addEventListener('mouseleave', function() {
           overlay.style.opacity = '0';
         });
       }

       // Ensure form fields are accessible
       const formFields = document.querySelectorAll('input, select, textarea');
       formFields.forEach(field => {
         field.style.position = 'relative';
         field.style.zIndex = '10';
       });

       // Initialize data attributes with current values
       function initializeDayPricing() {
         // Initialize weekday pricing
         const activeWeekdayButton = document.querySelector('#weekdaySelector .btn.active');
         if (activeWeekdayButton) {
           const weekdaySingle = document.getElementById('weekdaySingle');
           const weekdayDouble = document.getElementById('weekdayDouble');
           if (weekdaySingle) {
             activeWeekdayButton.setAttribute('data-single', weekdaySingle.value);
           }
           if (weekdayDouble) {
             activeWeekdayButton.setAttribute('data-double', weekdayDouble.value);
           }
         }

         // Initialize weekend pricing
         const activeWeekendButton = document.querySelector('#weekendSelector .btn.active');
         if (activeWeekendButton) {
           const weekendSingle = document.getElementById('weekendSingle');
           const weekendDouble = document.getElementById('weekendDouble');
           if (weekendSingle) {
             activeWeekendButton.setAttribute('data-single', weekendSingle.value);
           }
           if (weekendDouble) {
             activeWeekendButton.setAttribute('data-double', weekendDouble.value);
           }
         }
       }

       // Call initialization
       initializeDayPricing();

             // Add price validation and dynamic pricing
       const priceInputs = document.querySelectorAll('input[name*="Single"], input[name*="Double"]');
       priceInputs.forEach(input => {
         input.addEventListener('input', function() {
           const value = parseFloat(this.value);
           if (value < 0) {
             this.value = 0;
           }

           // Update the corresponding day button's data attributes
           if (this.id === 'weekdaySingle') {
             const activeWeekdayButton = document.querySelector('#weekdaySelector .btn.active');
             if (activeWeekdayButton) {
               activeWeekdayButton.setAttribute('data-single', this.value);
             }
           } else if (this.id === 'weekdayDouble') {
             const activeWeekdayButton = document.querySelector('#weekdaySelector .btn.active');
             if (activeWeekdayButton) {
               activeWeekdayButton.setAttribute('data-double', this.value);
             }
           } else if (this.id === 'weekendSingle') {
             const activeWeekendButton = document.querySelector('#weekendSelector .btn.active');
             if (activeWeekendButton) {
               activeWeekendButton.setAttribute('data-single', this.value);
             }
           } else if (this.id === 'weekendDouble') {
             const activeWeekendButton = document.querySelector('#weekendSelector .btn.active');
             if (activeWeekendButton) {
               activeWeekendButton.setAttribute('data-double', this.value);
             }
           }
         });

         // Also save on blur to ensure changes are captured
         input.addEventListener('blur', function() {
           const value = parseFloat(this.value);
           if (value < 0) {
             this.value = 0;
           }

           // Update the corresponding day button's data attributes
           if (this.id === 'weekdaySingle') {
             const activeWeekdayButton = document.querySelector('#weekdaySelector .btn.active');
             if (activeWeekdayButton) {
               activeWeekdayButton.setAttribute('data-single', this.value);
             }
           } else if (this.id === 'weekdayDouble') {
             const activeWeekdayButton = document.querySelector('#weekdaySelector .btn.active');
             if (activeWeekdayButton) {
               activeWeekdayButton.setAttribute('data-double', this.value);
             }
           } else if (this.id === 'weekendSingle') {
             const activeWeekendButton = document.querySelector('#weekendSelector .btn.active');
             if (activeWeekendButton) {
               activeWeekendButton.setAttribute('data-single', this.value);
             }
           } else if (this.id === 'weekendDouble') {
             const activeWeekendButton = document.querySelector('#weekendSelector .btn.active');
             if (activeWeekendButton) {
               activeWeekendButton.setAttribute('data-double', this.value);
             }
           }
         });
       });
    });

    function validateForm() {
      let isValid = true;
      const requiredFields = document.querySelectorAll('input[required], select[required]');

      requiredFields.forEach(field => {
        if (!validateField(field)) {
          isValid = false;
        }
      });

      // Validate price fields
      const priceFields = ['weekdaySingle', 'weekdayDouble', 'weekendSingle', 'weekendDouble'];
      priceFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && parseFloat(field.value) < 0) {
          showFieldError(field, 'Price must be greater than or equal to 0');
          isValid = false;
        }
      });

      return isValid;
    }

    function validateField(field) {
      const value = field.value.trim();

      if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
      }

      if (field.type === 'number' && value) {
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue < 0) {
          showFieldError(field, 'Please enter a valid positive number');
          return false;
        }
      }

      clearFieldError(field);
      return true;
    }

    function showFieldError(field, message) {
      clearFieldError(field);
      field.classList.add('is-invalid');

      const errorDiv = document.createElement('div');
      errorDiv.className = 'invalid-feedback';
      errorDiv.textContent = message;
      field.parentNode.appendChild(errorDiv);
    }

    function clearFieldError(field) {
      field.classList.remove('is-invalid');
      const errorDiv = field.parentNode.querySelector('.invalid-feedback');
      if (errorDiv) {
        errorDiv.remove();
      }
    }

    // Multiple image upload initialization is handled in the external JS file
  </script>

@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
  <style>
    .vehicle-type-images-container {
      min-height: 300px;
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      padding: 20px;
      background-color: #f8f9fa;
    }

    .vehicle-type-images-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }

    .vehicle-type-image-item {
      position: relative;
      border: 2px solid #dee2e6;
      border-radius: 8px;
      padding: 10px;
      background-color: white;
      transition: all 0.3s ease;
    }

    .vehicle-type-image-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .vehicle-type-image-item.border-primary {
      border-color: #0d6efd;
    }

    .vehicle-type-image-item img {
      border-radius: 4px;
    }
  </style>
@endpush

@push('scripts')
  <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
@endpush


