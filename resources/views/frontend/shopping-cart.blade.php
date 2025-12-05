@extends('layouts.frontend')

@section('title', 'Your order summary')

@section('content')
    <div class="container mt-5 default-page-marign-top">
        <h2 class="mb-4">
            Your order summary
            <small class="text-muted">
                Reservation Date: {{ $selected_date }}
            </small>
        </h2>
        @if ($guestCartItems->count() > 0)
            <div class="row">
                <div class="col-lg-8">
                    {{-- HEre show time slot --}}
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <table class="table table-borderless align-middle">
                                <thead class="border-bottom">
                                    <tr>
                                        <th>SL</th>
                                        <th width="50%">Package</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        {{-- <th>Action</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $subtotal = 0; @endphp

                                    @foreach ($guestCartItems as $key => $item)
                                        @php
                                            $itemTotal = $item->cart_amount * $item->quantity;
                                            $subtotal += $itemTotal;
                                        @endphp

                                        <tr class="border-bottom">
                                            <td>{{ ++$key }}</td>

                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded overflow-hidden" style="width: 80px; height: 60px;">
                                                        <img src="{{ $item->package->display_image_url ?? asset('images/default-package.jpg') }}"
                                                            alt="{{ $item->package->name ?? '' }}"
                                                            class="w-100 h-100 object-fit-cover">
                                                    </div>

                                                    <div>
                                                        <h6 class="mb-1">{{ $item->package->name ?? 'Package Name' }}</h6>
                                                        @if (isset($time_slot))
                                                            <div class="schedule-color small mt-1">
                                                                Schedule:
                                                                {{ $time_slot->name ?? $time_slot->start_time . ' - ' . $time_slot->end_time }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>TK {{ number_format($item->cart_amount, 2) }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="mx-3 fw-bold">{{ $item->quantity }}</span>
                                                    {{-- <form action="{{ route('frontend.cart.update') }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="cart_uuid"
                                                            value="{{ $item->cart_uuid }}">
                                                        <input type="hidden" name="change" value="minus">
                                                        <button type="submit"
                                                            class="btn btn-outline-secondary btn-sm px-3 py-1 @if ($item->quantity <= 1) disabled @endif">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </form>


                                                    <form action="{{ route('frontend.cart.update') }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="cart_uuid"
                                                            value="{{ $item->cart_uuid }}">
                                                        <input type="hidden" name="change" value="addition">
                                                        <button type="submit"
                                                            class="btn btn-outline-secondary btn-sm px-3 py-1">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </form> --}}
                                                </div>
                                            </td>

                                            <td><strong>TK {{ number_format($itemTotal, 2) }}</strong></td>

                                            {{-- <td>
                                                <form action="{{ route('frontend.cart.remove', $item->cart_uuid) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                                        onclick="return confirm('Remove this item from cart?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td> --}}
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    @php
                        $subtotal = $guestCartItems->sum(function ($item) {
                            return $item->cart_amount * $item->quantity;
                        });
                    @endphp
                    @include('frontend._order_summary', [
                        'guestCartItems' => $guestCartItems,
                        'subtotal' => $subtotal,
                    ])
                </div>

            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                </div>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any packages yet.</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-store me-2"></i>Browse Packages
                </a>
            </div>
        @endif
    </div>
@endsection


@push('styles')
    <style>
        .object-fit-cover {
            object-fit: cover;
        }

        .schedule-color {
            color: #e55a22;
            font-weight: bold;
        }

        .equal-btn {
            width: 100%;
            max-width: 325px;
            text-decoration: none;
        }

        .sticky-top {
            position: sticky;
            top: 20px;
        }

        .table tbody tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        .checkout-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 10px;
            height: 57px;
            background: #FC692A;
            color: #fff;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            transition: .3s;
        }

        .checkout-btn:hover {
            background: #e55a22;
            transform: translateY(-2px);
        }

        .continue-shopping-btn {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            border: 2px solid #FC692A;
            color: #FC692A;
            background: #fff;
            text-decoration: none;
            transition: .3s;
        }

        .continue-shopping-btn:hover {
            background: #FC692A;
            color: #fff;
        }

        @media(max-width: 576px) {
            .checkout-btn {
                height: 50px;
            }
        }
    </style>
@endpush
