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
        // Get active regular packages with their variants and prices
        // Filter by group type (capacity > 1) instead of hardcoded names
        $regularPackages = Package::with(['variants.prices'])
            ->where('is_active', true)
            ->where('type', 'regular')
            ->whereHas('variants', function($query) {
                $query->where('capacity', '>', 1); // Only group packages (capacity > 1)
            })
            ->orderBy('name')
            ->get();

        return view('frontend.index', compact('regularPackages'));
    }
}
