<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Package;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    /**
     * Display the homepage with regular packages for pricing section
     */
    public function home()
    {
        try {
            // Add logging to debug
            Log::info('Homepage accessed');
            
            $data['regularPackages'] = Package::with(['packagePrices.riderType'])
                ->where('is_active', true)
                ->where('type', 'regular')
                ->whereHas('packagePrices.riderType', function($query) {
                    $query->whereNotIn('slug', ['single-rider', '1-person']);
                })
                ->orderBy('name')
                ->get();
                
            Log::info('Regular packages loaded', $data);
            
            return view('frontend.index', $data);
            
        } catch (\Exception $e) {
            Log::error('Homepage error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback view if something goes wrong
            return view('frontend.index', ['regularPackages' => collect()]);
        }
    }
}