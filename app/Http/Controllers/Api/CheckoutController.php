<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function process(CheckoutRequest $request)
    {
        try {
            $reservation = $this->reservationService->createReservationFromCart(
                $request->only(['name', 'email', 'phone']),
                $request->promo_code
            );

            return response()->json([
                'success' => true,
                'message' => 'Reservation created successfully',
                'booking_code' => $reservation->booking_code,
                'redirect_url' => route('booking.confirmation', $reservation->booking_code) // Assuming named route
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
