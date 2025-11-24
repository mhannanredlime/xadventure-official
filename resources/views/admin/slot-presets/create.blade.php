@extends('layouts.admin')

@section('title', 'Create Time Slot Preset')

@section('content')
  <div class="content-area flex-grow-1">
    <h2 class="mb-3">Create Time Slot Preset</h2>
    <div class="card p-3">
      @if ($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger">
          {{ session('error') }}
        </div>
      @endif

      <form method="POST" action="{{ route('admin.slot-presets.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_active">Active</label>
        </div>
        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_default" id="is_default" {{ old('is_default') ? 'checked' : '' }}>
          <label class="form-check-label" for="is_default">Set as default</label>
        </div>

        <div class="mb-3">
          <label class="form-label">Select Slots</label>
          @error('slots')
            <div class="text-danger mb-2">{{ $message }}</div>
          @enderror
          <div class="row">
            @foreach($slots as $slot)
              <div class="col-md-6 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="slots[]" value="{{ $slot->id }}" id="slot{{ $slot->id }}" {{ in_array($slot->id, old('slots', [])) ? 'checked' : '' }}>
                  <label class="form-check-label" for="slot{{ $slot->id }}">{{ $slot->name }} ({{ \Carbon\Carbon::parse($slot->start_time)->format('g A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('g A') }})</label>
                </div>
              </div>
            @endforeach
          </div>
          @if($slots->isEmpty())
            <div class="text-warning">No active schedule slots found. Please create schedule slots first.</div>
          @endif
        </div>

        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('admin.slot-presets.index') }}" class="btn btn-link">Cancel</a>
      </form>
    </div>
  </div>
@endsection


