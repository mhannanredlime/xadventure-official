<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreReservationRequest;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Display all reservations for today and future dates only
     */
    public function index(Request $request)
    {
        $data['reservations'] = $this->reservationService->getFilteredReservations($request);
        $data['groupedReservations'] = $this->reservationService->groupReservationsByTransaction($data['reservations']);
        
        $data['data'] = $this->reservationService->getViewData();
        
        return view('admin.reservations.index', $data);
    }

    public function create()
    {
        $data = $this->reservationService->getViewData();
        return view('admin.reservations.index', $data);
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        $this->reservationService->createReservation($validated);

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'package', 'scheduleSlot', 'items', 'payments', 'promoRedemptions.promoCode']);
        $reservations = collect([$reservation]);
        $groupedReservations = $this->reservationService->groupReservationsByTransaction($reservations);
        
        return view('admin.reservations.index', compact('reservation', 'reservations', 'groupedReservations'));
    }

    public function edit(Reservation $reservation)
    {
        $data = $this->reservationService->getViewData();
        $reservations = collect([$reservation]);
        $groupedReservations = $this->reservationService->groupReservationsByTransaction($reservations);
        
        return view('admin.reservations.index', array_merge([
            'reservation' => $reservation,
            'reservations' => $reservations,
            'groupedReservations' => $groupedReservations
        ], $data));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
            'schedule_slot_id' => 'required|exists:schedule_slots,id',
            'date' => 'required|date',
            'party_size' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'required|in:pending,partial,paid,refunded',
            'notes' => 'nullable|string',
        ]);

        $this->reservationService->updateReservation($reservation, $validated);

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    public function destroy(Reservation $reservation)
    {
        $message = $this->reservationService->deleteReservation($reservation);

        return redirect()->route('admin.reservations.index')
            ->with('success', $message);
    }

    /**
     * Display reservation history for past dates only
     */
    public function history(Request $request)
    {
        $reservations = $this->reservationService->getFilteredReservations($request, true); // true = history
        $groupedReservations = $this->reservationService->groupReservationsByTransaction($reservations);
        $data = $this->reservationService->getViewData();

        return view('admin.reservations.index', array_merge([
            'reservations' => $reservations,
            'groupedReservations' => $groupedReservations,
        ], $data));
    }

    /**
     * Export current and future reservations to CSV
     */
    public function exportPending(Request $request)
    {
        try {
            $reservations = $this->reservationService->getFilteredReservations($request);
            return $this->reservationService->generateCsv($reservations, 'current_future_reservations_' . now()->format('Y-m-d'));
        } catch (\Exception $e) {
            Log::error('Export pending reservations failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export data. Please try again.');
        }
    }

    /**
     * Export reservation history to CSV
     */
    public function exportHistory(Request $request)
    {
        try {
            $reservations = $this->reservationService->getFilteredReservations($request, true); // true = history
            return $this->reservationService->generateCsv($reservations, 'reservation_history_' . now()->format('Y-m-d'));
        } catch (\Exception $e) {
            Log::error('Export reservation history failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export data. Please try again.');
        }
    }
}
