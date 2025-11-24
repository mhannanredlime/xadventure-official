@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Vehicle Types</h1>
        <a href="{{ route('admin.vehicle-types.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add Vehicle Type
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered responsive-stacked" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Vehicles</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicleTypes as $vehicleType)
                        <tr>
                            <td data-label="ID">{{ $vehicleType->id }}</td>
                            <td data-label="Image">
                                @if($vehicleType->primaryImageUrl)
                                    <img src="{{ $vehicleType->primaryImageUrl }}" alt="{{ $vehicleType->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                @elseif($vehicleType->image_path)
                                    <img src="{{ asset('storage/' . $vehicleType->image_path) }}" alt="{{ $vehicleType->name }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 50px; height: 50px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td data-label="Name">{{ $vehicleType->name }}</td>
                            <td data-label="Status">
                                <span class="badge badge-{{ $vehicleType->is_active ? 'success' : 'secondary' }}">
                                    {{ $vehicleType->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td data-label="Vehicles">{{ $vehicleType->vehicles->count() }}</td>
                            <td data-label="Created">{{ $vehicleType->created_at->format('M d, Y') }}</td>
                            <td data-label="Actions">
                                <a href="{{ route('admin.vehicle-types.edit', $vehicleType) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('admin.vehicle-types.destroy', $vehicleType) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle type?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
