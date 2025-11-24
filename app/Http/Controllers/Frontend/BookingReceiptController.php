<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingReceiptController extends Controller
{
    /**
     * Show booking receipt by booking code
     */
    public function show(Request $request, $bookingCode)
    {
        try {
            // Find the reservation by booking code
            $firstReservation = Reservation::with([
                'customer',
                'packageVariant.package',
                'scheduleSlot',
                'items',
                'payments'
            ])->where('booking_code', $bookingCode)->first();

            if (!$firstReservation) {
                Log::warning('Booking receipt accessed with invalid code', [
                    'booking_code' => $bookingCode,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => $bookingCode
                ]);
            }

            // Find all reservations from the same checkout session
            // Look for reservations with the same customer and created within 5 minutes of the first reservation
            $allReservations = Reservation::with([
                'customer',
                'packageVariant.package',
                'scheduleSlot',
                'items',
                'payments'
            ])
            ->where('customer_id', $firstReservation->customer_id)
            ->where('created_at', '>=', $firstReservation->created_at->subMinutes(5))
            ->where('created_at', '<=', $firstReservation->created_at->addMinutes(5))
            ->orderBy('created_at')
            ->get();

            // Calculate totals
            $totalAmount = $allReservations->sum('total_amount');
            $totalPartySize = $allReservations->sum('party_size');

            // Log the receipt access
            Log::info('Booking receipt accessed', [
                'reservation_id' => $firstReservation->id,
                'booking_code' => $firstReservation->booking_code,
                'customer_id' => $firstReservation->customer_id,
                'total_reservations' => $allReservations->count(),
                'total_amount' => $totalAmount,
                'total_party_size' => $totalPartySize,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return view('frontend.booking.receipt', [
                'reservation' => $firstReservation, // Keep for backward compatibility
                'allReservations' => $allReservations,
                'totalAmount' => $totalAmount,
                'totalPartySize' => $totalPartySize
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing booking receipt', [
                'booking_code' => $bookingCode,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return view('frontend.booking.receipt-error', [
                'bookingCode' => $bookingCode
            ]);
        }
    }

    /**
     * Show booking receipt by shortlink ID
     */
    public function showByShortlink(Request $request, $shortlinkId)
    {
        try {
            // Find the reservation by shortlink ID (we'll use a simple hash for now)
            $reservation = Reservation::with([
                'customer',
                'packageVariant.package',
                'scheduleSlot',
                'items',
                'payments'
            ])->where('id', $shortlinkId)->first();

            if (!$reservation) {
                Log::warning('Booking receipt accessed with invalid shortlink', [
                    'shortlink_id' => $shortlinkId,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return view('frontend.booking.receipt-not-found', [
                    'bookingCode' => 'Unknown'
                ]);
            }

            // Log the receipt access
            Log::info('Booking receipt accessed via shortlink', [
                'reservation_id' => $reservation->id,
                'booking_code' => $reservation->booking_code,
                'shortlink_id' => $shortlinkId,
                'customer_id' => $reservation->customer_id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return view('frontend.booking.receipt', [
                'reservation' => $reservation
            ]);

        } catch (\Exception $e) {
            Log::error('Error showing booking receipt via shortlink', [
                'shortlink_id' => $shortlinkId,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return view('frontend.booking.receipt-error', [
                'bookingCode' => 'Unknown'
            ]);
        }
    }
}
