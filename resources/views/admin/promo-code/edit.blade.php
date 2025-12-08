@extends('layouts.admin')

@section('title', 'Edit Promo Code')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Edit Promo Code: {{ $promoCode->code }}</h2>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Back to Promo Codes
                    </a>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.promo-codes.update', $promoCode) }}" method="POST">
                            @method('PUT')
                            @include('admin.promo-code._form', [
                                'promoCode' => $promoCode,
                                'packages' => $packages,
                                'vehicleTypes' => $vehicleTypes,
                            ])
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
