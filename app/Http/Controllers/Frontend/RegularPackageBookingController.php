<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Cart;
use App\Services\CartService;
use App\Http\Controllers\Controller;

class RegularPackageBookingController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    
    public function index()
    {
        $data['guestCartItems'] = Cart::with('package')
            ->where('session_id', session()->getId())
            ->where('created_at', '>=', now()->subMinutes(env('SESSION_LIFETIME')))
            ->get();
        return view('frontend.regular-packages-booking', $data);
    }

}
