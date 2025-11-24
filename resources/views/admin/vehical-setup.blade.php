@extends('layouts.admin')

@section('title', 'Vehicle Type Setup')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/vehical-management-setup.css') }}">
  <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
  <style>
    .vehicle-card .card-body {
      position: relative;
    }
    
    .vehicle-card .creation-date {
      font-size: 0.75rem;
      color: #6c757d;
      margin-bottom: 0.75rem;
    }
    
    .vehicle-card .creation-date i {
      margin-right: 0.25rem;
    }
    
    .dropdown-item.active {
      background-color: #0d6efd;
      color: white;
    }
    
    .sort-dropdown .btn {
      font-size: 0.875rem;
      padding: 0.375rem 0.75rem;
    }

    /* Responsive Design Improvements */
    .page-header {
      margin-bottom: 2rem;
    }

    .page-title-section {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .page-title {
      margin: 0;
      font-size: 1.75rem;
      font-weight: 600;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .vehicle-grid {
      margin-top: 1.5rem;
    }

    .vehicle-card {
      height: 100%;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .vehicle-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .vehicle-card .card-img-top {
      height: 200px;
      object-fit: cover;
    }

    .vehicle-card .card-title {
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
    }

    .vehicle-card .card-text {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }

    .vehicle-card-icons {
      margin-top: auto;
    }

    .vehicle-card-icons a {
      color: #6c757d;
      transition: color 0.2s ease;
    }

    .vehicle-card-icons a:hover {
      color: #0d6efd;
    }

    .empty-state {
      padding: 3rem 1rem;
      text-align: center;
    }

    .empty-state i {
      font-size: 3rem;
      color: #dee2e6;
      margin-bottom: 1rem;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1200px) {
      .page-title {
        font-size: 1.5rem;
      }
    }

    @media (max-width: 992px) {
      .page-header {
        margin-bottom: 1.5rem;
      }
      
      .page-title-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }

      .header-actions {
        width: 100%;
        justify-content: space-between;
      }

      .vehicle-card .card-img-top {
        height: 180px;
      }
    }

    @media (max-width: 768px) {
      .page-title {
        font-size: 1.25rem;
      }

      .header-actions {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
      }

      .sort-dropdown {
        align-self: flex-start;
      }

      .btn-add-vehicle {
        align-self: stretch;
      }

      .vehicle-card .card-img-top {
        height: 160px;
      }

      .vehicle-card .card-title {
        font-size: 1rem;
      }

      .vehicle-card .card-text {
        font-size: 0.85rem;
      }

      .creation-date {
        font-size: 0.7rem;
      }
    }

    @media (max-width: 576px) {
      .page-header {
        margin-bottom: 1rem;
      }

      .page-title {
        font-size: 1.1rem;
      }

      .vehicle-card .card-img-top {
        height: 140px;
      }

      .vehicle-card .card-body {
        padding: 1rem;
      }

      .vehicle-card-icons {
        justify-content: center;
      }

      .empty-state {
        padding: 2rem 1rem;
      }

      .empty-state i {
        font-size: 2.5rem;
      }
    }

    /* Container adjustments for sidebar */
    @media (min-width: 992px) {
      .content-container {
        margin-left: 2%;
        padding-top: 3%;
      }
    }

    @media (max-width: 991px) {
      .content-container {
        margin-left: 0;
        padding-top: 2%;
        padding-left: 1rem;
        padding-right: 1rem;
      }
    }
  </style>
@endpush

@section('content')
  <div class="content-container">
    <div class="page-header">
      <div class="page-title-section">
        <h2 class="page-title">Vehicle Type Setup</h2>
        <div class="header-actions">
          <div class="dropdown sort-dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-sort-down"></i> Sort by
            </button>
            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
              <li><a class="dropdown-item {{ $sort === 'newest' ? 'active' : '' }}" href="{{ route('admin.vehical-setup', ['sort' => 'newest']) }}">Newest First</a></li>
              <li><a class="dropdown-item {{ $sort === 'oldest' ? 'active' : '' }}" href="{{ route('admin.vehical-setup', ['sort' => 'oldest']) }}">Oldest First</a></li>
              <li><a class="dropdown-item {{ $sort === 'name' ? 'active' : '' }}" href="{{ route('admin.vehical-setup', ['sort' => 'name']) }}">Name A-Z</a></li>
              <li><a class="dropdown-item {{ $sort === 'name_desc' ? 'active' : '' }}" href="{{ route('admin.vehical-setup', ['sort' => 'name_desc']) }}">Name Z-A</a></li>
            </ul>
          </div>
          @can('vehicle-types.manage')
          <a href="{{ route('admin.vehicle-types.create') }}" class="btn btn-add-vehicle rounded-pill">
            <i class="bi bi-plus-lg me-2"></i> Add Vehicle Type
          </a>
          @endcan
        </div>
      </div>
    </div>
    
    <div class="vehicle-grid">
      <div class="row g-3 g-md-4" id="vehicleTypesContainer">
        @forelse($vehicleTypes as $vehicleType)
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3" id="vehicleType-{{ $vehicleType->id }}">
            <div class="card vehicle-card h-100">
              <img src="{{ $vehicleType->displayImageUrl }}" 
                   class="card-img-top" alt="{{ $vehicleType->name }}"
                   onerror="this.src='{{ asset('admin/images/vehicle-type.svg') }}'">
              <div class="card-body vehicle-card-body d-flex flex-column">
                <h5 class="card-title fw-bold">{{ $vehicleType->name }}</h5>
                <p class="card-text text-muted">{{ $vehicleType->subtitle ?: $vehicleType->seating_capacity . ' Seater ' . $vehicleType->name }}</p>
                <div class="creation-date">
                  <i class="bi bi-calendar3"></i> Created: {{ $vehicleType->created_at->format('M d, Y') }}
                </div>
                <div class="d-flex mt-auto vehicle-card-icons">
                  <a href="{{ route('admin.vehicle-types.edit', $vehicleType) }}" class="text-decoration-none me-3">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="#" class="text-decoration-none delete-vehicle-type" 
                     data-id="{{ $vehicleType->id }}" 
                     data-name="{{ $vehicleType->name }}">
                    <i class="bi bi-trash"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12">
            <div class="empty-state">
              <i class="bi bi-car-front"></i>
              <h4 class="text-muted">No Vehicle Types Found</h4>
              <p class="text-muted">Click "Add Vehicle Type" to create your first vehicle type.</p>
            </div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteVehicleTypeModal" tabindex="-1" aria-labelledby="deleteVehicleTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteVehicleTypeModalLabel">Confirm Delete</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete the vehicle type "<span id="deleteVehicleTypeName"></span>"?</p>
          <p class="text-danger"><small>This action cannot be undone.</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteVehicleType">Delete</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let deleteVehicleTypeId = null;

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
</script>
@endpush


