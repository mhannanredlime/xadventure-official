$(document).ready(function() {
    let deleteVehicleTypeId = null;

    // Load create form
    $('#createVehicleTypeModal').on('show.bs.modal', function() {
        $.get('/admin/vehicle-types/create', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                $('#createVehicleTypeFormContainer').html(response.html);
                // Initialize multiple image upload component after form is loaded
                setTimeout(() => {
                    const uploadContainer = document.getElementById('multiple-image-upload');
                    if (uploadContainer && typeof MultipleImageUpload !== 'undefined') {
                        new MultipleImageUpload(uploadContainer.id, {
                            maxFiles: parseInt(uploadContainer.dataset.maxFiles) || 10,
                            maxFileSize: parseInt(uploadContainer.dataset.maxFileSize) || 2 * 1024 * 1024
                        });
                    }
                }, 100);
            }
        });
    });

    // Load edit form
    $('.edit-vehicle-type').on('click', function() {
        const vehicleTypeId = $(this).data('id');
        $.get('/admin/vehicle-types/' + vehicleTypeId + '/edit', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(response) {
            if (response.success) {
                $('#editVehicleTypeFormContainer').html(response.html);
                // Initialize multiple image upload component after form is loaded
                setTimeout(() => {
                    const uploadContainer = document.getElementById('multiple-image-upload');
                    if (uploadContainer && typeof MultipleImageUpload !== 'undefined') {
                        new MultipleImageUpload(uploadContainer.id, {
                            maxFiles: parseInt(uploadContainer.dataset.maxFiles) || 10,
                            maxFileSize: parseInt(uploadContainer.dataset.maxFileSize) || 2 * 1024 * 1024
                        });
                    }
                }, 100);
            }
        });
    });

    // Handle create form submission
    $(document).on('submit', '#createVehicleTypeForm', function(e) {
        e.preventDefault();
        
        // Update form data with multiple images
        if (window.multipleImageUploadInstance) {
            const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
            const formImageInput = this.querySelector('input[name="images[]"]');
            if (selectedFiles.length > 0) {
                // Create a new FileList-like object
                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                formImageInput.files = dt.files;
            }
        }
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '/admin/vehicle-types',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#createVehicleTypeModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        const input = $('#' + key);
                        const errorDiv = $('#' + key + '_error');
                        input.addClass('is-invalid');
                        errorDiv.text(errors[key][0]);
                    });
                } else {
                    showAlert('error', 'An error occurred while creating the vehicle type.');
                }
            }
        });
    });

    // Handle edit form submission
    $(document).on('submit', '#editVehicleTypeForm', function(e) {
        e.preventDefault();
        
        // Update form data with multiple images
        if (window.multipleImageUploadInstance) {
            const selectedFiles = window.multipleImageUploadInstance.getSelectedFiles();
            const formImageInput = this.querySelector('input[name="images[]"]');
            if (selectedFiles.length > 0) {
                // Create a new FileList-like object
                const dt = new DataTransfer();
                selectedFiles.forEach(file => dt.items.add(file));
                formImageInput.files = dt.files;
            }
        }
        
        const vehicleTypeId = $(this).data('id') || $('.edit-vehicle-type[data-bs-target="#editVehicleTypeModal"]').data('id');
        const formData = new FormData(this);
        
        $.ajax({
            url: '/admin/vehicle-types/' + vehicleTypeId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#editVehicleTypeModal').modal('hide');
                    showAlert('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    Object.keys(errors).forEach(function(key) {
                        const input = $('#edit_' + key);
                        const errorDiv = $('#edit_' + key + '_error');
                        input.addClass('is-invalid');
                        errorDiv.text(errors[key][0]);
                    });
                } else {
                    showAlert('error', 'An error occurred while updating the vehicle type.');
                }
            }
        });
    });

    // Handle delete
    $('.delete-vehicle-type').on('click', function(e) {
        e.preventDefault();
        deleteVehicleTypeId = $(this).data('id');
        const vehicleTypeName = $(this).data('name');
        $('#deleteVehicleTypeName').text(vehicleTypeName);
        $('#deleteVehicleTypeModal').modal('show');
    });

    // Confirm delete
    $('#confirmDeleteVehicleType').on('click', function() {
        if (!deleteVehicleTypeId) return;
        
        $.ajax({
            url: '/admin/vehicle-types/' + deleteVehicleTypeId,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteVehicleTypeModal').modal('hide');
                    showAlert('success', response.message);
                    $('#vehicleType-' + deleteVehicleTypeId).fadeOut();
                }
            },
            error: function() {
                showAlert('error', 'An error occurred while deleting the vehicle type.');
            }
        });
    });

    // Clear form validation on modal close
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').text('');
    });

    // Helper function to show alerts
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the container
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
});
