<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Package;
use App\Models\ScheduleSlot;
use App\Services\CartService;

class CustomPackageController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the custom packages page with all necessary data
     */
    public function index()
    {
        $data['page_title'] = 'Regular Packages';
        $data['allPackages'] = Package::with([
            'packagePrices',
            'images',
        ])
            ->where('is_active', true)
            ->where('type', 'regular')
            ->orderBy('created_at', 'desc')
            ->get();

        // Cart count
        $cartCount = $this->cartService->getCartTotalItems();

        return view('frontend.custom-packages', $data);
    }

    /**
     * Display the regular packages booking page (Cart & Schedule)
     */
    public function booking()
    {
        $data['guestCartItems'] = $this->cartService->getCartItems();
        $data['time_slots'] = ScheduleSlot::where('is_active', true)->get();

        return view('frontend.regular-packages-booking', $data);
    }
}
