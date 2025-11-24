@extends('layouts.admin')

@section('title', 'Time Slot Presets')

@section('content')
  <div class="flex-grow-1 pt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="mb-0">Time Slot Presets</h2>
      @can('calendar.manage')
      <a href="{{ route('admin.slot-presets.create') }}" class="btn btn-primary">Create Preset</a>
      @endcan
    </div>

    <div class="card p-3">
      <table class="table align-middle responsive-stacked">
        <thead>
          <tr>
            <th>Name</th>
            <th>Items</th>
            <th>Status</th>
            <th>Default</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($presets as $preset)
            <tr>
              <td data-label="Name">{{ $preset->name }}</td>
              <td data-label="Items">{{ $preset->items_count }}</td>
              <td data-label="Status">{!! $preset->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
              <td data-label="Default">{!! $preset->is_default ? '<span class="badge bg-primary">Default</span>' : '' !!}</td>
              <td data-label="Actions" class="text-end">
                @can('calendar.manage')
                <a href="{{ route('admin.slot-presets.edit', $preset) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form action="{{ route('admin.slot-presets.destroy', $preset) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this preset?')">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
                @if(!$preset->is_default)
                  <form action="{{ route('admin.slot-presets.make-default', $preset) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">Make Default</button>
                  </form>
                @endif
                @endcan
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted">No presets yet</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection


