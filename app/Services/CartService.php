<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get the current cart count
     */
    public function getCartCount(): int
    {
        $cart = Session::get('cart', []);
        return count($cart);
    }

    /**
     * Get cart items with details
     */
    public function getCartItems(): array
    {
        $cart = Session::get('cart', []);
        $items = [];

        foreach ($cart as $key => $item) {
            $items[] = [
                'key' => $key,
                'package_id' => $item['package_id'],
                'quantity' => $item['quantity'],
                'date' => $item['date'],
                'slot_id' => $item['slot_id'],
            ];
        }

        return $items;
    }

    /**
     * Add item to cart
     */
    public function addToCart(array $item): void
    {
        $cart = Session::get('cart', []);
        $key = $item['package_id'] . '_' . $item['date'] . '_' . $item['slot_id'];
        $cart[$key] = $item;
        Session::put('cart', $cart);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(string $key): void
    {
        $cart = Session::get('cart', []);
        unset($cart[$key]);
        Session::put('cart', $cart);
    }

    /**
     * Clear cart
     */
    public function clearCart(): void
    {
        Session::forget('cart');
    }

    /**
     * Check if cart is empty
     */
    public function isCartEmpty(): bool
    {
        return $this->getCartCount() === 0;
    }

    /**
     * Get cart total items (sum of quantities)
     */
    public function getCartTotalItems(): int
    {
        $cart = Session::get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['quantity'] ?? 1;
        }

        return $total;
    }
}
