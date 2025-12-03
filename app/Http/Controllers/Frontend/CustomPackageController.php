<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
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
        // Load all active packages with required relations
        $data['allPackages'] = Package::with([
            'packagePrices',
            'images',
        ])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Cart count
        $cartCount = $this->cartService->getCartTotalItems();

        return view('frontend.custom-packages', $data);
    }
}
