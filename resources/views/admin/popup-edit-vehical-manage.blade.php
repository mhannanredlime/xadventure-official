@extends('layouts.admin')

@section('title', 'Vehicle Management - Details')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/popup-edit-save-vehical-manage.css') }}">
@endpush

@section('content')
  <div class="main-content">
    <div class="card main-card shadow-sm">
      <div class="card-body p-4">
        <div class="card-header-custom bg-white px-0 pb-4 d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-0">Vehicle Management</h4>
            <p class="breadcrumb-custom">Vehicle Management > <span class="active-breadcrumb">Details</span></p>
          </div>
          <div class="header-actions d-flex align-items-center gap-2">
            <div class="form-check form-switch d-flex align-items-center">
              <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" checked>
              <span class="ms-2">Active</span>
            </div>
            <a href="{{ url('/admin/popup-save-vehical-manage') }}" class="btn btn-edit jatio-bg-color">Edit</a>
          </div>
        </div>
        <div class="row pt-3">
          <div class="col-lg-5"><img src="images/pack-2.png" class="img-fluid rounded" alt="ATV Image"></div>
          <div class="col-lg-7 ps-lg-5">
            <div class="row">
              <div class="col-md-6 mb-4"><p class="detail-label">Vehicle Type</p><p class="detail-value">ATV</p></div>
              <div class="col-md-6 mb-4"><p class="detail-label">Vehicle Name</p><p class="detail-value">ATV 1</p></div>
              <div class="col-md-6 mb-4"><p class="detail-label">Details</p><p class="detail-value">Yellow</p></div>
              <div class="col-md-6 mb-4"><p class="detail-label">Operation Start Date</p><p class="detail-value">02 Feb, 2025</p></div>
              <div class="col-12"><p class="detail-label">Notes</p><p class="detail-value">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


