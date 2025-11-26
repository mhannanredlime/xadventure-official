@extends('layouts.admin')

@section('title', 'Vehicle Management')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/vehical-management-setup.css') }}">
  <style>
    .modal-content {
      border-radius: 15px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .modal-header {
      border-bottom: 1px solid #e9ecef;
      padding: 1.5rem;
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      color: white;
      border-radius: 15px 15px 0 0;
    }

    .modal-body {
      padding: 2rem;
    }

    .modal-footer {
      border-top: 1px solid #e9ecef;
      padding: 1.5rem;
    }

    .btn-close {
      filter: invert(1);
    }

    .form-label {
      font-weight: 600;
      color: #495057;
      margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      padding: 0.75rem;
      transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: #ff6b35;
      box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
    }

    .btn-primary-custom {
      background: linear-gradient(135deg, #ff6b35, #f7931e);
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(255, 107, 53, 0.4);
    }

    .btn-secondary-custom {
      background: #6c757d;
      border: none;
      border-radius: 8px;
      padding: 0.75rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-secondary-custom:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }

    .vehicle-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
      border: 2px solid #e9ecef;
    }

    .action-icons a, .action-icons button {
      margin: 0 0.25rem;
      padding: 0.5rem;
      border-radius: 6px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .action-icons a:hover, .action-icons button:hover {
      background-color: #f8f9fa;
      transform: scale(1.1);
    }

    .action-icon {
      transition: all 0.3s ease;
      filter: brightness(0.7);
    }

    .action-icons a:hover .action-icon {
      filter: brightness(1);
      transform: scale(1.1);
    }

    .action-icons a[title="Edit"]:hover .action-icon {
      filter: brightness(0) saturate(100%) invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%);
    }

    .action-icons a[title="View"]:hover .action-icon {
      filter: brightness(0) saturate(100%) invert(27%) sepia(51%) saturate(2878%) hue-rotate(346deg) brightness(104%) contrast(97%);
    }

    .toggle-icon.active {
      color: #28a745;
    }

    .toggle-icon.inactive {
      color: #6c757d;
    }

    .action-icons button:hover .toggle-icon.active {
      color: #dc3545;
    }

    .action-icons button:hover .toggle-icon.inactive {
      color: #28a745;
    }

    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-size: 0.875rem;
      font-weight: 600;
    }

    .status-active {
      background-color: #d4edda;
      color: #155724;
    }

    .status-inactive {
      background-color: #f8d7da;
      color: #721c24;
    }

    .alert {
      border-radius: 10px;
      border: none;
      padding: 1rem 1.5rem;
    }

    .alert-success {
      background: linear-gradient(135deg, #d4edda, #c3e6cb);
      color: #155724;
    }

    .alert-danger {
      background: linear-gradient(135deg, #f8d7da, #f5c6cb);
      color: #721c24;
    }

    .loading-spinner {
      display: none;
    }

    .loading .loading-spinner {
      display: inline-block;
    }

    .image-preview {
      max-width: 200px;
      max-height: 200px;
      border-radius: 8px;
      border: 2px solid #e9ecef;
    }

    .form-check-input:checked {
      background-color: #ff6b35;
      border-color: #ff6b35;
    }
  </style>
@endpush

@section('content')
  <main class="main-content">
    <div class="dashboard-header">
      <h1>Vehicle Management</h1>
      <div class="header-actions">
        <button class="btn btn-filter" id="filterBtn">
          <i class="bi bi-funnel"></i> All Vehicles
        </button>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-add-new">
          <i class="bi bi-plus-circle"></i> Add New Vehicle
        </a>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="alertContainer"></div>

    <div class="table-container">
      <div class="table-responsive">
        <table class="table responsive-stacked">
          <thead>
            <tr>
              <th>Image</th>
              <th>Vehicle Type</th>
              <th>Vehicle Name</th>
              <th>Details</th>
              <th>Status</th>
              <th>Start Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="vehiclesTableBody">
            @forelse($vehicles as $vehicle)
              <tr data-vehicle-id="{{ $vehicle->id }}">
                <td data-label="Image">
                  @php
                    $displayImage = null;


                    // If no vehicle images, try vehicle type images
                    if (!$displayImage && $vehicle->vehicleType) {
                        $vehicleTypePrimaryImage = $vehicle->vehicleType->primaryImage()->first();
                        if ($vehicleTypePrimaryImage) {
                            $displayImage = $vehicleTypePrimaryImage->url;
                        } else {
                            $vehicleTypeFirstImage = $vehicle->vehicleType->images()->first();
                            if ($vehicleTypeFirstImage) {
                                $displayImage = $vehicleTypeFirstImage->url;
                            }
                        }
                    }

                  @endphp

                  <img src="{{ $displayImage }}" alt="{{ $vehicle->name }}" class="vehicle-img"
                       data-fallback="{{ asset('admin/images/vehicle-type.svg') }}"
                       onerror="this.src=this.dataset.fallback">
                </td>
                <td data-label="Vehicle Type">{{ $vehicle->vehicleType->name ?? 'N/A' }}</td>
                <td data-label="Vehicle Name">{{ $vehicle->name }}</td>
                <td data-label="Details">{{ $vehicle->details ?? 'N/A' }}</td>
                <td data-label="Status">
                  <span class="status-badge {{ $vehicle->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $vehicle->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td data-label="Start Date">{{ $vehicle->op_start_date ? date('d M, Y', strtotime($vehicle->op_start_date)) : 'N/A' }}</td>
                <td data-label="Action" class="action-icons">
                  <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-link p-0" title="Edit">
                    <img src="{{ asset('admin/images/edit.svg') }}" alt="Edit" class="action-icon" style="width: 20px; height: 20px;">
                  </a>
                  <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn btn-link p-0" title="View">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="action-icon">
                      <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/>
                      <path d="M9 9l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M15 9l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </a>
                  <button class="btn btn-link p-0 toggle-status-btn" data-vehicle-id="{{ $vehicle->id }}" title="{{ $vehicle->is_active ? 'Deactivate' : 'Activate' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="action-icon toggle-icon {{ $vehicle->is_active ? 'active' : 'inactive' }}">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                      <path d="M12 2v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M12 18v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M4.93 4.93l2.83 2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M16.24 16.24l2.83 2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M2 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M18 12h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M4.93 19.07l2.83-2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      <path d="M16.24 7.76l2.83-2.83" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </button>
                  <button class="btn btn-link p-0 delete-vehicle-btn" data-vehicle-id="{{ $vehicle->id }}" title="Delete">
                    <i class="bi bi-trash text-danger"></i>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-4">
                  <i class="bi  bi-motorcycle fa-3x text-muted mb-3 d-block"></i>
                  <h5>No Vehicles Found</h5>
                  <p class="text-muted">Add your first vehicle to get started.</p>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pagination-controls">
        <div class="d-flex align-items-center gap-2">
          <span>Total Vehicles: <span id="totalVehicles">{{ $vehicles->count() }}</span></span>
        </div>
      </div>
    </div>
  </main>



  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
            <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status"></span>
            Delete Vehicle
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentVehicleId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // Toggle Status Button
    $(document).on('click', '.toggle-status-btn', function() {
        const vehicleId = $(this).data('vehicle-id');
        const button = $(this);

        $.ajax({
            url: `/admin/vehicles/${vehicleId}/toggle`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    const row = button.closest('tr');
                    const statusCell = row.find('td:eq(4)');

                    if (response.is_active) {
                        statusCell.html('<span class="status-badge status-active">Active</span>');
                        button.find('.toggle-icon').removeClass('inactive').addClass('active');
                        button.attr('title', 'Deactivate');
                    } else {
                        statusCell.html('<span class="status-badge status-inactive">Inactive</span>');
                        button.find('.toggle-icon').removeClass('active').addClass('inactive');
                        button.attr('title', 'Activate');
                    }

                    showAlert(response.message, 'success');
                } else {
                    showAlert('Error updating vehicle status', 'danger');
                }
            },
            error: function() {
                showAlert('Error updating vehicle status', 'danger');
            }
        });
    });

    // Delete Vehicle Button
    $(document).on('click', '.delete-vehicle-btn', function() {
        currentVehicleId = $(this).data('vehicle-id');
        deleteModal.show();
    });

    // Confirm Delete
    $('#confirmDeleteBtn').click(function() {
        const button = $(this);
        button.addClass('loading').prop('disabled', true);

        $.ajax({
            url: `/admin/vehicles/${currentVehicleId}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $(`tr[data-vehicle-id="${currentVehicleId}"]`).fadeOut(300, function() {
                        $(this).remove();
                        updateTotalCount();
                    });
                    deleteModal.hide();
                    showAlert(response.message, 'success');
                } else {
                    showAlert('Error deleting vehicle', 'danger');
                }
            },
            error: function() {
                showAlert('Error deleting vehicle', 'danger');
            },
            complete: function() {
                button.removeClass('loading').prop('disabled', false);
            }
        });
    });





    // Helper Functions
    function updateTotalCount() {
        const count = $('#vehiclesTableBody tr').length;
        $('#totalVehicles').text(count);
    }

    function updateTotalCount() {
        const count = $('#vehiclesTableBody tr').length;
        $('#totalVehicles').text(count);
    }

    function showAlert(message, type) {
        const toastType = type === 'danger' ? 'error' : type;
        toastNotifications[toastType](message);
    }

    // Initialize multiple image upload
    document.addEventListener('DOMContentLoaded', function() {
        const uploadContainer = document.getElementById('multiple-image-upload');
        if (uploadContainer) {
            new MultipleImageUpload('multiple-image-upload', {
                maxFiles: parseInt(uploadContainer.dataset.maxFiles) || 10,
                maxFileSize: parseInt(uploadContainer.dataset.maxFileSize) || 2 * 1024 * 1024
            });
        }
    });
});
</script>

@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
@endpush



