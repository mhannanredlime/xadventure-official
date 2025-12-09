<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Payment;
use App\Models\ScheduleSlot;
use App\Models\VehicleType;
use App\Models\Customer;
use App\Models\Package;
use App\Models\RiderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use App\Events\BookingStatusUpdated;

class ReservationService
{
    /**
     * Get filtered reservations based on request parameters
     *
     * @param Request $request
     * @param bool $isHistory
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFilteredReservations(Request $request, bool $isHistory = false)
    {
        $query = Reservation::with(['customer', 'package', 'scheduleSlot']);

        // Date range filtering
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        } else {
            $today = now()->format('Y-m-d');
            if ($isHistory) {
                // Default: Show only past dates for history
                $query->where('date', '<', $today);
            } else {
                // Default: Show only today and future dates for active reservations
                $query->where('date', '>=', $today);
            }
        }

        // Filter by vehicle type
        if ($request->filled('vehicle_type')) {
            $vehicleType = $request->vehicle_type;
            $query->whereHas('package.vehicleTypes', function ($q) use ($vehicleType) {
                $q->where('name', $vehicleType);
            });
        }

        // Filter by booking status
        if ($request->filled('booking_status')) {
            $query->where('booking_status', $request->booking_status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        } elseif (!$isHistory) {
             // Default: Show only paid reservations for dashboard if no filter applied
            $query->where('payment_status', 'paid');
        } else {
            // For history, default is also paid unless filtered
             if (!$request->filled('payment_status') && !$request->filled('booking_status')) {
                 $query->where('payment_status', 'paid');
             }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('package.vehicleTypes', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        if ($isHistory) {
            $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('date', 'asc')->orderBy('created_at', 'desc');
        }

        return $query->get();
    }

    /**
     * Group reservations by transaction_id
     */
    public function groupReservationsByTransaction($reservations)
    {
        $grouped = collect();
        
        foreach ($reservations as $reservation) {
            // Get the transaction_id from the reservation's payment
            $payment = $reservation->payments()->latest()->first();
            $transactionId = $payment ? $payment->transaction_id : null;
            
            if ($transactionId) {
                // Check if we already have a group for this transaction
                $existingGroup = $grouped->firstWhere('transaction_id', $transactionId);
                
                if ($existingGroup) {
                    // Add this reservation to the existing group
                    $existingGroup['reservations']->push($reservation);
                } else {
                    // Create a new group for this transaction
                    $grouped->push([
                        'transaction_id' => $transactionId,
                        'primary_reservation' => $reservation, // Use the first reservation as primary
                        'reservations' => collect([$reservation]),
                        'total_amount' => $reservation->total_amount,
                        'customer' => $reservation->customer,
                        'booking_status' => $reservation->booking_status,
                        'payment_status' => $reservation->payment_status,
                        'date' => $reservation->date,
                        'created_at' => $reservation->created_at,
                    ]);
                }
            } else {
                // If no transaction_id, treat as individual reservation
                $grouped->push([
                    'transaction_id' => null,
                    'primary_reservation' => $reservation,
                    'reservations' => collect([$reservation]),
                    'total_amount' => $reservation->total_amount,
                    'customer' => $reservation->customer,
                    'booking_status' => $reservation->booking_status,
                    'payment_status' => $reservation->payment_status,
                    'date' => $reservation->date,
                    'created_at' => $reservation->created_at,
                ]);
            }
        }
        
        // Calculate total amounts for grouped reservations
        $grouped->transform(function ($group) {
            if ($group['reservations']->count() > 1) {
                $group['total_amount'] = $group['reservations']->sum('total_amount');
            }
            return $group;
        });
        
        return $grouped;
    }

    /**
     * Create a new reservation
     */
    public function createReservation(array $validatedData)
    {
        $validatedData['booking_code'] = $this->generateBookingCode();
        $validatedData['report_time'] = ScheduleSlot::find($validatedData['schedule_slot_id'])->report_time;

        return Reservation::create($validatedData);
    }

    /**
     * Update an existing reservation
     */
    public function updateReservation(Reservation $reservation, array $validatedData)
    {
        $oldBookingStatus = $reservation->booking_status;
        
        $validatedData['report_time'] = ScheduleSlot::find($validatedData['schedule_slot_id'])->report_time;

        $reservation->update($validatedData);

        // Check if booking status changed and dispatch event
        if ($oldBookingStatus !== $validatedData['booking_status']) {
            // Load the reservation with relationships for SMS
            $reservationWithRelations = Reservation::with(['customer', 'package'])->find($reservation->id);
            event(new BookingStatusUpdated($reservationWithRelations, $oldBookingStatus, $validatedData['booking_status'], [
                'updated_by' => 'Admin',
                'update_time' => now()->toDateTimeString(),
            ]));
        }

        return $reservation;
    }

    /**
     * Delete a reservation (handing multi-package logic)
     */
    public function deleteReservation(Reservation $reservation)
    {
        // Get the transaction_id from the reservation's payment
        $payment = $reservation->payments()->latest()->first();
        $transactionId = $payment ? $payment->transaction_id : null;
        
        if ($transactionId) {
            // Find all reservations with the same transaction_id
            $allPayments = Payment::where('transaction_id', $transactionId)->get();
            $reservationIds = $allPayments->pluck('reservation_id')->toArray();
            
            // Delete all reservations in the group
            $deletedCount = Reservation::whereIn('id', $reservationIds)->delete();
            
            // Delete all payments in the group
            Payment::where('transaction_id', $transactionId)->delete();
            
            return $deletedCount > 1 
                ? "Multi-package booking deleted successfully. {$deletedCount} reservations removed."
                : "Reservation deleted successfully.";
        } else {
            // Single reservation without transaction_id
            $reservation->delete();
            return "Reservation deleted successfully.";
        }
    }

    /**
     * Generate CSV file from reservations
     */
    public function generateCsv($reservations, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function() use ($reservations) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Booking Code',
                'Date',
                'Package Name',
                'Vehicle Types',
                'Report Time',
                'Schedule Slot',
                'Guest Name',
                'Guest Email',
                'Guest Phone',
                'Party Size',
                'Subtotal',
                'Discount',
                'Tax',
                'Total Amount',
                'Deposit Amount',
                'Balance Amount',
                'Booking Status',
                'Payment Status',
                'Notes',
                'Created At'
            ]);

            // CSV Data
            foreach ($reservations as $reservation) {
                $vehicleTypes = $reservation->package->vehicleTypes->pluck('name')->implode(', ');
                
                fputcsv($file, [
                    $reservation->booking_code,
                    $reservation->date->format('m/d/Y'),
                    $reservation->package->name ?? 'N/A',
                    $vehicleTypes,
                    $reservation->report_time ? $reservation->report_time->format('g:i A') : 'N/A',
                    $reservation->scheduleSlot ? $reservation->scheduleSlot->name : 'N/A',
                    $reservation->customer->name ?? 'N/A',
                    $reservation->customer->email ?? 'N/A',
                    $reservation->customer->phone ?? 'N/A',
                    $reservation->party_size,
                    number_format($reservation->subtotal, 2),
                    number_format($reservation->discount_amount ?? 0, 2),
                    number_format($reservation->tax_amount ?? 0, 2),
                    number_format($reservation->total_amount, 2),
                    number_format($reservation->deposit_amount ?? 0, 2),
                    number_format($reservation->balance_amount ?? 0, 2),
                    ucfirst($reservation->booking_status),
                    ucfirst($reservation->payment_status),
                    $reservation->notes ?? '',
                    $reservation->created_at->format('m/d/Y g:i A')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }

    // Helper to get common view data
    public function getViewData()
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'packages' => Package::where('is_active', true)->orderBy('name')->get(),
            'riderTypes' => RiderType::orderBy('id')->get(),
            'scheduleSlots' => ScheduleSlot::orderBy('sort_order')->get(),
            'vehicleTypes' => VehicleType::where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
