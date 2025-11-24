<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVariant;
use App\Models\ScheduleSlot;
use App\Services\CartService;
use Illuminate\Http\Request;

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
        // Get active bundle packages (packages with Bundle variant)
        $bundlePackages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->whereHas('variants', function($q) {
                $q->where('variant_name', 'Bundle')->where('is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active packages of type 'regular' with their relationships (excluding bundle packages)
        $packages = Package::with(['variants.prices', 'vehicleTypes', 'images'])
            ->where('is_active', true)
            ->where('type', 'regular')
            ->whereDoesntHave('variants', function($q) {
                $q->where('variant_name', 'Bundle');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get active package variants that belong to 'regular' packages (excluding bundle variants)
        $packageVariants = PackageVariant::with(['package', 'prices'])
            ->whereHas('package', function($q) {
                $q->where('type', 'regular')->where('is_active', true);
            })
            ->where('is_active', true)
            ->where('variant_name', '!=', 'Bundle')
            ->get();

        // Get bundle package variants
        $bundleVariants = PackageVariant::with(['package', 'prices'])
            ->whereHas('package', function($q) {
                $q->where('is_active', true);
            })
            ->where('is_active', true)
            ->where('variant_name', 'Bundle')
            ->get();

        // Organize package variants by their respective packages
        $variantsByPackage = [];
        foreach ($packages as $package) {
            $variantsByPackage[$package->id] = $packageVariants->filter(function($variant) use ($package) {
                return $variant->package_id == $package->id;
            })->values();
        }

        // Organize bundle variants by their respective packages
        $bundleVariantsByPackage = [];
        foreach ($bundlePackages as $package) {
            $bundleVariantsByPackage[$package->id] = $bundleVariants->filter(function($variant) use ($package) {
                return $variant->package_id == $package->id;
            })->values();
        }

        // Get active schedule slots ordered by sort_order and start_time
        $scheduleSlots = ScheduleSlot::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->get();

        // Get cart count
        $cartCount = $this->cartService->getCartTotalItems();

        return view('frontend.custom-packages', compact(
            'packages', 
            'packageVariants', 
            'variantsByPackage', 
            'bundlePackages',
            'bundleVariants',
            'bundleVariantsByPackage',
            'scheduleSlots', 
            'cartCount'
        ));
    }
}
