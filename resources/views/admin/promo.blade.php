@extends('layouts.admin')

@section('title', 'Promo Codes')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/promo-popup.css') }}">
@endpush

@section('content')
  <main class="container-main">
    <div class="d-flex justify-content-between align-items-center mb-4 page-title-row">
      <h1 class="h3 page-title">Promo Codes</h1>
    </div>

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

    <div class="card">
      <div class="card-header bg-white p-3">
        <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-start align-items-md-center gap-2">
          <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
            
            <div class="input-group">
              <select class="form-select" id="vehicleFilter">
                <option value="">All Vehicles</option>
                @foreach($vehicleTypes ?? [] as $vehicleType)
                  <option value="{{ $vehicleType->id }}">{{ $vehicleType->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="input-group">
              <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="expired">Expired</option>
              </select>
            </div>
          </div>
          <div>
            @can('promo-codes.manage')
            <button class="btn btn-add-new jatio-bg-color" onclick="openPromoModal()">
              <i class="bi bi-plus-lg"></i> Add New Promo Code
            </button>
            @endcan
          </div>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 responsive-stacked" id="promoCodesTable">
            <thead>
              <tr>
                <th>Promo Code</th>
                <th>Calculation (% or Flat)</th>
                <th>Discount Amount</th>
                <th>Start Date</th>
                <th>Expire Date</th>
                <th>Status</th>
                <th>Remarks</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($promoCodes as $promoCode)
                <tr data-package="{{ $promoCode->package_id }}" data-vehicle="{{ $promoCode->vehicle_type_id }}" data-status="{{ $promoCode->status }}">
                  
                  <td data-label="Promo Code"><strong>{{ $promoCode->code }}</strong></td>
                  <td data-label="Type/Value">{{ $promoCode->discount_type === 'percentage' ? $promoCode->discount_value . '%' : '৳ ' . number_format($promoCode->discount_value) }}</td>
                  <td data-label="Max Discount">
                    @if($promoCode->max_discount)
                      Max: ৳ {{ number_format($promoCode->max_discount) }}
                    @else
                      -
                    @endif
                  </td>
                  <td data-label="Start Date">{{ $promoCode->starts_at ? date('d M, Y', strtotime($promoCode->starts_at)) : 'No Start Date' }}</td>
                  <td data-label="Expire Date">{{ $promoCode->ends_at ? date('d M, Y', strtotime($promoCode->ends_at)) : 'No End Date' }}</td>
                  <td data-label="Status">
                    @php
                      $statusClass = '';
                      switch($promoCode->status) {
                        case 'active':
                          $statusClass = 'active';
                          break;
                        case 'inactive':
                          $statusClass = 'inactive';
                          break;
                        case 'expired':
                          $statusClass = 'expired';
                          break;
                      }
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($promoCode->status) }}</span>
                  </td>
                  <td data-label="Remarks">{{ Str::limit($promoCode->remarks, 30) }}</td>
                  <td data-label="Action" class="text-center action-icons">
                    @can('promo-codes.manage')
                    <button onclick="editPromoCode(this.dataset.promoId)" data-promo-id="{{ $promoCode->id }}" title="Edit" class="btn btn-link p-0">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="deletePromoCode(this.dataset.promoId)" data-promo-id="{{ $promoCode->id }}" title="Delete" class="btn btn-link p-0">
                      <i class="bi bi-telephone-x text-danger"></i>
                    </button>
                    <button onclick="togglePromoStatus(this.dataset.promoId)" data-promo-id="{{ $promoCode->id }}" title="Toggle Status" class="btn btn-link p-0">
                      <i class="bi bi-telephone-minus text-success"></i>
                    </button>
                    @endcan
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-4">
                    <i class="bi  bi-tag fa-3x text-muted mb-3 d-block"></i>
                    <h5>No Promo Codes Found</h5>
                    <p class="text-muted">Create your first promo code to get started.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer bg-white d-flex justify-content-between align-items-center table-footer">
        <div class="d-flex align-items-center">
          <span class="me-3">Total Promo Codes: <span id="totalCount">{{ $promoCodes->count() }}</span></span>
          <span class="me-3">Showing: <span id="showingCount">{{ $promoCodes->count() }}</span></span>
        </div>
      </div>
    </div>
  </main>

  <!-- Promo Code Modal -->
  <div id="promoModal" class="modal-overlay" style="display: none;">
    <div class="modal-backdrop-link" onclick="closePromoModal()"></div>
    <div class="promo-modal-content">
      <div class="promo-modal-header">
        <h2 id="modalTitle">Add Promo Code</h2>
        <button onclick="closePromoModal()" class="btn-close-modal">&times;</button>
      </div>
      <div class="promo-modal-body mb-5">
        <form id="promoForm" method="POST" action="/admin/promo-codes">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="promo_id" id="promoId">

          <div class="mb-3">
            <label for="appliesTo" class="form-label">Applies To</label>
            <select class="form-select form-control-custom" id="appliesTo" name="applies_to" required>
              <option value="all">All Packages</option>
              <option value="package">Specific Package</option>
              <option value="vehicle_type">Specific Vehicle Type</option>
            </select>
          </div>

          <div class="mb-3" id="packageSelectGroup" style="display: none;">
            <label for="packageName" class="form-label">Package Name</label>
            <select class="form-select form-control-custom" id="packageName" name="package_id">
              <option value="">Select Package</option>
              @foreach($packages ?? [] as $package)
                <option value="{{ $package->id }}">{{ $package->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3" id="vehicleSelectGroup" style="display: none;">
            <label for="vehicleType" class="form-label">Vehicle Type</label>
            <select class="form-select form-control-custom" id="vehicleType" name="vehicle_type_id">
              <option value="">Select Vehicle Type</option>
              @foreach($vehicleTypes ?? [] as $vehicleType)
                <option value="{{ $vehicleType->id }}">{{ $vehicleType->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label for="promoCode" class="form-label">Promo Code</label>
            <input type="text" class="form-control form-control-custom" id="promoCode" name="code" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Set Discount Type</label>
            <div class="btn-group w-100 discount-type-group" role="group" aria-label="Discount Type">
              <input type="radio" class="btn-check" name="discount_type" id="percentage" value="percentage" autocomplete="off" checked required>
              <label class="btn btn-outline-primary" for="percentage">Percentage</label>
              <input type="radio" class="btn-check" name="discount_type" id="fixed" value="fixed" autocomplete="off" required>
              <label class="btn btn-outline-primary" for="fixed">Flat (৳)</label>
            </div>
          </div>

          <div class="mb-3">
            <label for="discountValue" class="form-label">Discount Value</label>
            <input type="number" class="form-control form-control-custom" id="discountValue" name="discount_value" step="0.01" min="0" required>
          </div>

          <div class="mb-3">
            <label for="maxDiscount" class="form-label">Maximum Discount (Optional)</label>
            <input type="number" class="form-control form-control-custom" id="maxDiscount" name="max_discount" step="0.01" min="0">
          </div>

          <div class="mb-3">
            <label for="minSpend" class="form-label">Minimum Spend (Optional)</label>
            <input type="number" class="form-control form-control-custom" id="minSpend" name="min_spend" step="0.01" min="0">
          </div>

          <div class="mb-3">
            <label for="usageLimitTotal" class="form-label">Total Usage Limit (Optional)</label>
            <input type="number" class="form-control form-control-custom" id="usageLimitTotal" name="usage_limit_total" min="1">
          </div>

          <div class="mb-3">
            <label for="usageLimitPerUser" class="form-label">Usage Limit Per User</label>
            <input type="number" class="form-control form-control-custom" id="usageLimitPerUser" name="usage_limit_per_user" min="1" value="1" required>
          </div>

          <div class="mb-3">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" class="form-control form-control-custom" id="startDate" name="starts_at">
          </div>

          <div class="mb-3">
            <label for="expireDate" class="form-label">Expire Date</label>
            <input type="date" class="form-control form-control-custom" id="expireDate" name="ends_at">
          </div>

          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control form-control-custom" id="remarks" name="remarks" rows="3"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Set Discount Status</label>
            <div class="btn-group w-100 discount-status-group" role="group" aria-label="Discount Status">
              <input type="radio" class="btn-check" name="status" id="active" value="active" autocomplete="off" checked required>
              <label class="btn btn-outline-primary" for="active">Active</label>
              <input type="radio" class="btn-check" name="status" id="inactive" value="inactive" autocomplete="off" required>
              <label class="btn btn-outline-primary" for="inactive">Inactive</label>
            </div>
          </div>
        </form>
      </div>
      <div class="promo-modal-footer">
        <button type="button" class="btn btn-add-new text-white jatio-bg-color" onclick="savePromoCode()">Save</button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="{{ asset('admin/js/promo-codes.js') }}"></script>
@endpush


