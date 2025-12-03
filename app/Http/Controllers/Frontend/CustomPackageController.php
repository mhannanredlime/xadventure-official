<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVariant;
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
        // Load all active packages with required relations
        $allPackages = Package::with([
            'variants' => function ($q) {
                $q->where('is_active', true)->with('prices');
            },
            'packagePrices',
            'vehicleTypes',
            'images',
        ])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Split into bundle + regular packages
        $bundlePackages = $allPackages->filter(function ($p) {
            return $p->variants->where('variant_name', 'Bundle')->isNotEmpty();
        })->values();

        $packages = $allPackages->filter(function ($p) {
            return $p->type === 'regular' &&
                   $p->variants->where('variant_name', 'Bundle')->isEmpty();
        })->values();

        // Load all active variants once
        $allVariants = PackageVariant::with(['package', 'prices'])
            ->where('is_active', true)
            ->get();

        // Group variants by package
        $variantsByPackage = $allVariants
            ->where('variant_name', '!=', 'Bundle')
            ->groupBy('package_id');

        $bundleVariantsByPackage = $allVariants
            ->where('variant_name', 'Bundle')
            ->groupBy('package_id');

        // Schedule slots
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        // Cart count
        $cartCount = $this->cartService->getCartTotalItems();

        return view('frontend.custom-packages', compact(
            'packages',
            'variantsByPackage',
            'bundlePackages',
            'bundleVariantsByPackage',
            'scheduleSlots',
            'cartCount'
        ));
    }
}
