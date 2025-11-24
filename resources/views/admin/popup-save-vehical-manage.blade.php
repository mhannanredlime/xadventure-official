@extends('layouts.admin')

@section('title', 'Add New Vehicle')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/popup-edit-save-vehical-manage.css') }}">
@endpush

@section('content')
  <div class="main-content-vehical-manage">
    <div class="content-header d-flex justify-content-between align-items-center">
      <div>
        <h1>Vehicle Management</h1>
        <p class="breadcrumb">Vehicle Management > Add New Vehicle</p>
      </div>
      <button class="btn btn-primary-custom jatio-bg-color">Save New Vehicle</button>
    </div>
    <div class="main-card">
      <div class="row g-4">
        <div class="col-12 col-md-5">
          <div class="main-image-container"><img src="images/pack-2.png" alt="ATV" class="main-image"></div>
        </div>
        <div class="col-12 col-md-7">
          <div class="row g-4">
            <div class="col-12 col-md-6"><label for="vehicleType" class="form-label">Vehicle Type</label><select id="vehicleType" class="form-select"><option selected>ATV</option><option>Dirt Bike</option><option>Snowmobile</option></select></div>
            <div class="col-12 col-md-6"><label for="vehicleName" class="form-label">Vehicle Name</label><input type="text" class="form-control" id="vehicleName" value="ATV 1"></div>
            <div class="col-12 col-md-6"><label for="details" class="form-label">Details</label><input type="text" class="form-control" id="details" value="Yellow"></div>
            <div class="col-12 col-md-6"><label for="startDate" class="form-label">Operation Start Date</label><input type="date" class="form-control" id="startDate" value="2025-02-02"></div>
            <div class="col-12"><label for="notes" class="form-label">Notes</label><textarea class="form-control" id="notes" rows="4" placeholder="Type here..."></textarea></div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


