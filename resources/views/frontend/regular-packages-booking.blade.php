@extends('layouts.frontend')

@section('title', 'Your Cart')

@section('content')
    <div class="container mt-5 default-page-marign-top">
        <h2>Your Cart</h2>

        @if ($guestCartItems->count() > 0)
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Base Price (TK)</th>
                        <th>Quantity</th>
                        <th>Total (TK)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($guestCartItems as $item)
                        <tr>
                            <td class="d-flex align-items-center gap-2">
                                <img src="{{ $item->package->display_image_url ?? '' }}"
                                    alt="{{ $item->package->name ?? '' }}" width="60" height="40">
                                <span>{{ $item->package->name ?? '' }}</span>
                            </td>
                            <td>
                                TK {{ number_format($item->cart_amount, 2) }}
                            </td>
                            <td class="d-flex align-items-center gap-1">
                                <form action="{{ route('frontend.cart.update') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="cart_uuid" value="{{ $item->cart_uuid }}">
                                    <input type="hidden" name="change" value="minus">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary px-3">-</button>
                                </form>

                                <span class="px-2">{{ $item->quantity }}</span>

                                <form action="{{ route('frontend.cart.update') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="cart_uuid" value="{{ $item->cart_uuid }}">
                                    <input type="hidden" name="change" value="addition">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary px-3">+</button>
                                </form>
                            </td>

                            <td>
                                TK {{ number_format($item->cart_amount * $item->quantity, 2) }}
                            </td>
                            <td>
                                <form action="{{ route('frontend.cart.remove', $item->cart_uuid) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to remove this item?')">
                                        Remove
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end mt-3">
                <form action="#" method="GET">
                    <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
                </form>
            </div>
        @else
            <p>Your cart is empty.</p>
        @endif
    </div>
@endsection
