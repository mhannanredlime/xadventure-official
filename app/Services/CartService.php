<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get the current cart count (total quantity)
     */
    public function getCartCount(): int
    {
        return $this->getQuery()->sum('quantity');
    }

    /**
     * Get unique items count
     */
    public function getCartTotalItems(): int
    {
        return $this->getQuery()->count();
    }

    /**
     * Check if cart is empty
     */
    public function isCartEmpty(): bool
    {
        return $this->getCartTotalItems() === 0;
    }

    /**
     * Get cart items with details
     */
    public function getCartItems()
    {
        return $this->getQuery()->with(['package.images', 'package.vehicleTypes'])->get();
    }

    /**
     * Add item to cart
     */
    public function addToCart(array $item): void
    {
        $query = $this->getQuery();
        
        $existingItem = $query->where('package_id', $item['package_id'])
            ->where('rider_type_id', $item['rider_type_id'] ?? null)
            ->where('date', $item['date'] ?? null)
            ->where('schedule_slot_id', $item['slot_id'] ?? null)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $item['quantity'];
            $existingItem->save();
        } else {
            // Create new
            $data = [
                'package_id' => $item['package_id'],
                'package_type' => 'regular', // Default or fetch from package
                'rider_type_id' => $item['rider_type_id'] ?? null,
                'quantity' => $item['quantity'],
                'amount' => $item['amount'] ?? 0,
                'date' => $item['date'] ?? null,
                'schedule_slot_id' => $item['slot_id'] ?? null,
                'session_id' => Session::getId(),
            ];

            if (Auth::check()) {
                $data['user_id'] = Auth::id();
            } else {
                // Ensure session_id is set (already set above)
            }
            
            // Fetch package type if needed
            $package = Package::find($item['package_id']);
            if ($package) {
                $data['package_type'] = $package->type;
            }

            Cart::create($data);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($uuid): void
    {
        $this->getQuery()->where('uuid', $uuid)->delete();
    }

    /**
     * Clear cart and promo session data
     */
    public function clearCart(): void
    {
        $this->getQuery()->delete();
        
        // Also clear promo code session data
        Session::forget('applied_promo_code');
        Session::forget('promo_discount');
    }
    
    /**
     * Calculate totals
     */
    public function calculateTotals(PricingService $pricingService = null)
    {
        // Simple sum of amount * quantity stored in DB
        // Or recalculate if amount in DB is stale?
        // Let's rely on DB amount for now, or recalculate if needed.
        // Given 'amount' in carts table, let's use it.
        
        return $this->getQuery()->get()->sum(function ($item) {
            return $item->amount * $item->quantity;
        });
    }

    /**
     * Get base query for current user/session
     */
    protected function getQuery()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id());
        } else {
            return Cart::where('session_id', Session::getId());
        }
    }

    /**
     * Helper to get Rider Type Label
     */
    protected function getRiderTypeLabel($riderTypeId)
    {
        if (!$riderTypeId) return 'Standard';
        if ($riderTypeId == 1) return 'Single Rider';
        if ($riderTypeId == 2) return 'Double Rider';
        // Needs generic lookup if RiderType model exists and is robust
        return 'Variant #' . $riderTypeId;
    }
    /**
     * Get cart item by UUID
     */
    public function getCartItem(string $uuid)
    {
        return $this->getQuery()->where('uuid', $uuid)->first();
    }

    /**
     * Update cart item quantity
     */
    public function updateCartItem(string $uuid, string $action): bool
    {
        $cartItem = $this->getCartItem($uuid);

        if (!$cartItem) {
            return false;
        }

        if ($action === 'addition') {
            $cartItem->quantity += 1;
        } elseif ($action === 'minus') {
            if ($cartItem->quantity > 1) {
                $cartItem->quantity -= 1;
            } else {
                return false; // Cannot decrease below 1
            }
        }

        return $cartItem->save();
    }
}
