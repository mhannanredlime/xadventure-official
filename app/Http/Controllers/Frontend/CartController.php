<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart count for AJAX requests
     */
    public function getCartCount(Request $request)
    {
        return response()->json([
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total_items' => $this->cartService->getCartTotalItems(),
            'is_empty' => $this->cartService->isCartEmpty()
        ]);
    }

    /**
     * Get cart items for AJAX requests
     */
    public function getCartItems(Request $request)
    {
        return response()->json([
            'items' => $this->cartService->getCartItems(),
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total_items' => $this->cartService->getCartTotalItems()
        ]);
    }

    /**
     * Get cart status for AJAX requests
     */
    public function getCartStatus(Request $request)
    {
        return response()->json([
            'has_items' => !$this->cartService->isCartEmpty(),
            'cart_count' => $this->cartService->getCartCount(),
            'cart_total_items' => $this->cartService->getCartTotalItems()
        ]);
    }
}
