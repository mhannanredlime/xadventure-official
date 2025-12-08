@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Create New Role</h2>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Roles
                    </a>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.roles.store') }}" method="POST">
                            
                            @include('admin.roles._form', ['role' => null, 'permissions' => $permissions])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
