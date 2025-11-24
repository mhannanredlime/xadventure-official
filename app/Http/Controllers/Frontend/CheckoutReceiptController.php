<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutReceiptController extends Controller
{
    /**
     * Show checkout receipt by checkout ID
     */
    public function show(Request $request, $checkoutId)
    {
        try {
            // Parse checkout ID to get customer ID and first reservation ID
            if (!preg_match('/^checkout_(\d+)_(\d+)$/', $checkoutId, $matches)) {
                Log::warning('Invalid checkout ID format', [
                    'checkout_id' => $checkoutId,
                    'ip' => $request->ip(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => 'Invalid Checkout'
                ]);
            }

            $customerId = $matches[1];
            $firstReservationId = $matches[2];

            // Find the customer
            $customer = Customer::find($customerId);
            if (!$customer) {
                Log::warning('Customer not found for checkout receipt', [
                    'customer_id' => $customerId,
                    'checkout_id' => $checkoutId,
                    'ip' => $request->ip(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => 'Customer Not Found'
                ]);
            }

            // Find the first reservation to get the checkout date/time
            $firstReservation = Reservation::with(['packageVariant.package', 'scheduleSlot'])
                ->where('id', $firstReservationId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$firstReservation) {
                Log::warning('First reservation not found for checkout receipt', [
                    'customer_id' => $customerId,
                    'first_reservation_id' => $firstReservationId,
                    'checkout_id' => $checkoutId,
                    'ip' => $request->ip(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => 'Reservation Not Found'
                ]);
            }

            // Find all reservations for this customer on the same date (same checkout)
            $checkoutDate = $firstReservation->date;
            $reservations = Reservation::with([
                'customer',
                'packageVariant.package',
                'scheduleSlot',
                'items',
                'payments'
            ])
            ->where('customer_id', $customerId)
            ->where('date', $checkoutDate)
            ->where('created_at', '>=', $firstReservation->created_at->subMinutes(30)) // Within 30 minutes of first reservation
            ->orderBy('created_at', 'asc')
            ->get();

            if ($reservations->isEmpty()) {
                Log::warning('No reservations found for checkout receipt', [
                    'customer_id' => $customerId,
                    'checkout_date' => $checkoutDate,
                    'checkout_id' => $checkoutId,
                    'ip' => $request->ip(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => 'No Reservations Found'
                ]);
            }

            // Calculate total amount for all reservations
            $totalAmount = $reservations->sum('total_amount');
            $bookingCodes = $reservations->pluck('booking_code')->toArray();

            // Log the checkout receipt access
            Log::info('Checkout receipt accessed', [
                'checkout_id' => $checkoutId,
                'customer_id' => $customerId,
                'reservation_count' => $reservations->count(),
                'total_amount' => $totalAmount,
                'booking_codes' => $bookingCodes,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return view('frontend.booking.checkout-receipt', [
                'customer' => $customer,
                'reservations' => $reservations,
                'firstReservation' => $firstReservation,
                'totalAmount' => $totalAmount,
                'bookingCodes' => $bookingCodes,
                'checkoutId' => $checkoutId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing checkout receipt', [
                'checkout_id' => $checkoutId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return view('frontend.booking.receipt-error', [
                'bookingCode' => 'Checkout Error'
            ]);
        }
    }
}

