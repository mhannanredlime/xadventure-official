<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVariant;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage with regular packages for pricing section
     */
    public function index()
    {
        
        $regularPackages = Package::with(['packagePrices.riderType'])
            ->where('is_active', true)
            ->where('type', 'regular')
            ->whereHas('packagePrices.riderType', function($query) {
                // Approximate capacity > 1 check by excluding single rider types
                $query->whereNotIn('slug', ['single-rider', '1-person']);
            })
            ->orderBy('name')
            ->get();

        return view('frontend.index', compact('regularPackages'));
    }
}
