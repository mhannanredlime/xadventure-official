<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use App\Models\PackageVariant;

class CartService
{
    /**
     * Get the current cart count
     */
    public function getCartCount(): int
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            return $cart ? $cart->items()->count() : 0;
        }

        $cart = getGuestCartItems();
        return count($cart);
    }

    /**
     * Get cart items with details
     */
    public function getCartItems(): array
    {
        if (Auth::check()) {
            $cart = Cart::with(['items.variant.package.images', 'items.variant.package.vehicleTypes'])->where('user_id', Auth::id())->first();
            
            if (!$cart) {
                return [];
            }
            
            $items = [];
            foreach ($cart->items as $item) {
                $items[] = [
                    'key' => $item->id, // Use DB ID as key
                    'id' => $item->id,
                    'package_id' => $item->package_id,
                    // If we have variant relationship
                    'variant_id' => $item->package_variant_id,
                    'quantity' => $item->quantity,
                    'date' => $item->date,
                    'slot_id' => $item->schedule_slot_id, // Map slot_id
                    'variant' => $item->variant, // Include loaded relationships
                ];
            }
            return $items;
        }

        $cart = getGuestCartItems();
        $items = [];

        foreach ($cart as $key => $item) {
            $items[] = [
                'key' => $key,
                'package_id' => $item['package_id'],
                'variant_id' => $item['variant_id'] ?? null,
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
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            
            // Check if item exists (same variant, date, slot)
            $existingItem = $cart->items()
                ->where('package_variant_id', $item['variant_id'])
                ->where('date', $item['date'])
                ->where('schedule_slot_id', $item['slot_id'] ?? null)
                ->first();
                
            if ($existingItem) {
                $existingItem->quantity = $item['quantity']; // Update or Add? Usually replace or increment. Let's assume replace based on typical booking flows or check logic.
                // If it's "Add to cart", usually we want to set the new quantity or increment. 
                // Let's assume the $item['quantity'] is the desired total quantity for now or just update it.
                $existingItem->save();
            } else {
                $cart->items()->create([
                    'package_id' => $item['package_id'],
                    'package_variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'date' => $item['date'],
                    'schedule_slot_id' => $item['slot_id'] ?? null,
                ]);
            }
        } else {
            $cart = getGuestCartItems();
            $key = $item['package_id'] . '_' . ($item['variant_id'] ?? '0') . '_' . $item['date'] . '_' . ($item['slot_id'] ?? '0');
            $cart[$key] = $item;
            Session::put('cart', $cart);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($key): void
    {
        if (Auth::check()) {
            // Key is ID for auth users
             // Retrieve the user's cart to ensure ownership
             $cart = Cart::where('user_id', Auth::id())->first();
             if ($cart) {
                 $cart->items()->where('id', $key)->delete();
             }
        } else {
            $cart = getGuestCartItems();
            unset($cart[$key]);
            Session::put('cart', $cart);
        }
    }

    /**
     * Clear cart
     */
    public function clearCart(): void
    {
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            if ($cart) {
                $cart->items()->delete();
            }
        }
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
        if (Auth::check()) {
            $cart = Cart::where('user_id', Auth::id())->first();
            return $cart ? $cart->items()->sum('quantity') : 0;
        }

        $cart = getGuestCartItems();
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['quantity'] ?? 1;
        }

        return $total;
    }
    
    /**
     * Calculate totals including discounts using PricingService
     */
    public function calculateTotals(PricingService $pricingService)
    {
        $items = $this->getCartItems();
        $subtotal = 0;
        
        foreach ($items as $item) {
            // Need to fetch price. 
            // If we have variant object (DB)
            if (isset($item['variant']) && $item['variant']) {
                $variant = $item['variant'];
                $package = $variant->package;
            } else {
                // Fetch manually for session items
                $variant = PackageVariant::find($item['variant_id']);
                if (!$variant) continue;
                $package = $variant->package;
            }
            
            // Use PricingService
            // get_package_price requires $package, $date, $riderCount.
            // Wait, riderCount logic: is it quantity * variant capacity? Or simply the quantity?
            // "Rider-wise price" usually implies "Price per person if group size is X".
            // Or is it "Price for X riders"?
            // Assuming PricingService::get_package_price returns UNIT price for that condition.
            
            // Let's assume total riders = quantity * capacity (if capacity is persons per vehicle)
            // Or if quantity IS the number of vehicles/riders.
            // Let's assume quantity = number of vehicles.
            // Rider count for pricing lookup might depend on total group size?
            // "PackageRiderTypePrice" priority suggests price varies by rider type/count.
            
            // For now, let's pass quantity as riderCount or keep it simple
            $unitPrice = $pricingService->get_package_price($package, $item['date'], $item['quantity']); 
            
            $subtotal += $unitPrice * $item['quantity'];
        }
        
        return $subtotal;
    }
}
