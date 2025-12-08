@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Edit Role: {{ $role->name }}</h2>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Roles
                    </a>
                </div>

                @if ($role->is_system)
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        This is a system role. Some restrictions may apply.
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                           
                            @method('PUT')

                            @include('admin.roles._form', ['role' => $role, 'permissions' => $permissions])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
