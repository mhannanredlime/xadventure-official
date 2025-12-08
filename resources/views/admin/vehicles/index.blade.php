@extends('layouts.admin')
@section('title', 'Vehicle Management')
@section('content')
    <main class="main-content" x-data="vehicleManager()">
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
                    <tbody>
                        @forelse($vehicles as $vehicle)
                            <tr :class="{ 'opacity-50': processingId === {{ $vehicle->id }} }">
                                <td data-label="Image">
                                    @php
                                        $displayImage = null;
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
                                    <span class="status-badge"
                                        :class="vehicleStates[{{ $vehicle->id }}].isActive ? 'status-active' :
                                            'status-inactive'"
                                        x-text="vehicleStates[{{ $vehicle->id }}].isActive ? 'Active' : 'Inactive'">
                                    </span>
                                </td>
                                <td data-label="Start Date">
                                    {{ $vehicle->op_start_date ? date('d M, Y', strtotime($vehicle->op_start_date)) : 'N/A' }}
                                </td>
                                <td data-label="Action">
                                    <div class="action-icons">
                                        <a href="{{ route('admin.vehicles.edit', $vehicle) }}"
                                            class="btn-icon text-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="{{ route('admin.vehicles.show', $vehicle) }}" class="btn-icon text-info"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button class="btn-icon"
                                            :class="vehicleStates[{{ $vehicle->id }}].isActive ? 'text-success' :
                                                'text-secondary'"
                                            @click="toggleStatus({{ $vehicle->id }})"
                                            :disabled="processingId === {{ $vehicle->id }}"
                                            :title="vehicleStates[{{ $vehicle->id }}].isActive ? 'Deactivate' : 'Activate'">
                                            <i class="bi fs-5"
                                                :class="vehicleStates[{{ $vehicle->id }}].isActive ? 'bi-toggle-on' :
                                                    'bi-toggle-off'"></i>
                                        </button>
                                        <button class="btn-icon text-danger" @click="confirmDelete({{ $vehicle->id }})"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-motorcycle fa-3x text-muted mb-3 d-block"></i>
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
                    <span>Total Vehicles: <span>{{ $vehicles->count() }}</span></span>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal (Alpine controlled) -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" x-ref="deleteModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" @click="deleteVehicle" :disabled="isDeleting">
                            <span x-show="isDeleting" class="spinner-border spinner-border-sm me-2"></span>
                            Delete Vehicle
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
        <script src="{{ asset('admin/js/multiple-image-upload.js') }}"></script>
        <script>
            function vehicleManager() {
                return {
                    vehicleStates: {
                        @foreach ($vehicles as $vehicle)
                            {{ $vehicle->id }}: {
                                isActive: @json($vehicle->is_active)
                            },
                        @endforeach
                    },
                    processingId: null,
                    vehicleIdToDelete: null,
                    deleteModal: null,
                    isDeleting: false,

                    init() {
                        this.deleteModal = new bootstrap.Modal(this.$refs.deleteModal);
                    },

                    async toggleStatus(id) {
                        this.processingId = id;
                        try {
                            const response = await fetch(`/admin/vehicles/${id}/toggle`, {
                                method: 'PATCH',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                const data = await response.json();
                                if (data.success) {
                                    this.vehicleStates[id].isActive = data.is_active;
                                    Toast.fire({
                                        icon: 'success',
                                        title: data.message
                                    });
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: data.message || 'Error updating status'
                                    });
                                }
                            } else {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Network error occurred'
                                });
                            }
                        } catch (error) {
                            Toast.fire({
                                icon: 'error',
                                title: 'An unexpected error occurred'
                            });
                        } finally {
                            this.processingId = null;
                        }
                    },

                    confirmDelete(id) {
                        this.vehicleIdToDelete = id;
                        this.deleteModal.show();
                    },

                    async deleteVehicle() {
                        if (!this.vehicleIdToDelete) return;

                        this.isDeleting = true;
                        try {
                            const response = await fetch(`/admin/vehicles/${this.vehicleIdToDelete}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.ok) {
                                const data = await response.json();
                                if (data.success) {
                                    window.location.reload();
                                } else {
                                    Toast.fire({
                                        icon: 'error',
                                        title: data.message || 'Error deleting vehicle'
                                    });
                                }
                            } else {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Network error occurred'
                                });
                            }
                        } catch (error) {
                            Toast.fire({
                                icon: 'error',
                                title: 'An unexpected error occurred'
                            });
                        } finally {
                            this.isDeleting = false;
                            this.deleteModal.hide();
                            this.vehicleIdToDelete = null;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/multiple-image-upload.css') }}">
@endpush
