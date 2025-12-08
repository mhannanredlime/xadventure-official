<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;
    protected $pricingService;

    public function __construct(CartService $cartService, PricingService $pricingService)
    {
        $this->cartService = $cartService;
        $this->pricingService = $pricingService;
    }

    public function index()
    {
        $items = $this->cartService->getCartItems();
        $total = $this->cartService->calculateTotals($this->pricingService);
        
        return response()->json([
            'count' => count($items),
            'items' => $items,
            'subtotal' => $total
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'variant_id' => 'required|exists:package_variants,id',
            'date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'slot_id' => 'nullable|exists:schedule_slots,id'
        ]);

        $this->cartService->addToCart($request->all());

        return response()->json(['message' => 'Item added to cart', 'cart_count' => $this->cartService->getCartCount()]);
    }

    public function destroy($key)
    {
        $this->cartService->removeFromCart($key);
        return response()->json(['message' => 'Item removed', 'cart_count' => $this->cartService->getCartCount()]);
    }
}
