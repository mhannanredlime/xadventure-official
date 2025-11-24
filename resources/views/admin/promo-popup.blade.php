@extends('layouts.admin')

@section('title', 'Add Promo Code')

@push('styles')
  <link rel="stylesheet" href="{{ asset('admin/css/promo-popup.css') }}">
@endpush

@section('content')
  <div class="modal-overlay" style="min-height: 80vh;">
    <a href="{{ url('/admin/promo') }}" class="modal-backdrop-link"></a>
    <div class="promo-modal-content">
      <div class="promo-modal-header">
        <h2>Add Promo Code</h2>
        <a href="{{ url('/admin/promo') }}" class="btn-close-modal">&times;</a>
      </div>
      <div class="promo-modal-body mb-5">
        <form>
          <div class="mb-3">
            <label for="packageName" class="form-label">Package Name</label>
            <select class="form-select form-control-custom" id="packageName"><option selected>ATV Xtreme Adventure</option><option value="1">Archery</option><option value="2">ATV Ride</option><option value="3">Camping</option></select>
          </div>
          <div class="mb-3">
            <label for="promoCode" class="form-label">Promo Code</label>
            <input type="text" class="form-control form-control-custom" id="promoCode" value="EXT2025">
          </div>
          <div class="mb-3">
            <label class="form-label">Set Discount Type</label>
            <div class="btn-group w-100 discount-type-group" role="group" aria-label="Discount Type">
              <input type="radio" class="btn-check" name="discountType" id="percentage" autocomplete="off" checked>
              <label class="btn btn-outline-primary" for="percentage">Percentage</label>
              <input type="radio" class="btn-check" name="discountType" id="flat" autocomplete="off">
              <label class="btn btn-outline-primary" for="flat">Flat (৳)</label>
            </div>
          </div>
          <div class="mb-3">
            <label for="discountAmount" class="form-label">Discount Amount</label>
            <input type="number" class="form-control form-control-custom" id="discountAmount" value="25%">
          </div>
          <div class="mb-3">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" class="form-control form-control-custom" id="startDate" value="2025-02-02">
          </div>
          <div class="mb-3">
            <label for="expireDate" class="form-label">Expire Date</label>
            <input type="date" class="form-control form-control-custom" id="expireDate" value="2025-02-02">
          </div>
          <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <input type="text" class="form-control form-control-custom" id="remarks" value="Lorem ipsum dolor sit amet">
          </div>
          <div class="mb-3">
            <label class="form-label">Set Discount Status</label>
            <div class="btn-group w-100 discount-status-group" role="group" aria-label="Discount Status">
              <input type="radio" class="btn-check" name="discountStatus" id="active" autocomplete="off" checked>
              <label class="btn btn-outline-primary" for="active">Active</label>
              <input type="radio" class="btn-check" name="discountStatus" id="inactive" autocomplete="off">
              <label class="btn btn-outline-primary" for="inactive">Inactive</label>
            </div>
          </div>
        </form>
      </div>
      <div class="promo-modal-footer">
        <button type="button" class="btn btn-add-new text-white jatio-bg-color">Save</button>
      </div>
    </div>
  </div>
@endsection


