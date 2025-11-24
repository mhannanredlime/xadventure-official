<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PackageVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class RegularPackageBookingController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the regular package booking page with date/time selection
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $promoDiscount = 0;

        // Get cart items with package details
        foreach ($cart as $key => $item) {
            $variant = PackageVariant::with(['package', 'prices'])->find($item['variant_id']);
            if ($variant && $variant->package->type === 'regular') {
                $cartItems[$key] = [
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'date' => $item['date'] ?? null,
                    'slot_id' => $item['slot_id'] ?? null,
                ];
            }
        }

        // Get cart count
        $cartCount = $this->cartService->getCartTotalItems();

        return view('frontend.regular-packages-booking', compact('cartItems', 'cartCount', 'promoDiscount'));
    }
}
