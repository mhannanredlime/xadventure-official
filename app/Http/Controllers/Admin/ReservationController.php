<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\VehicleType;
use App\Models\ScheduleSlot;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\RiderType;
use App\Models\PackagePrice;
use Illuminate\Support\Facades\Log;
use App\Events\BookingStatusUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReservationController extends Controller
{
    /**
     * Display all reservations for today and future dates only
     * This is the main reservation dashboard for managing current and upcoming bookings
     */
    public function index(Request $request)
    {

        $query = Reservation::with(['customer', 'package', 'scheduleSlot']);

        // Date range filtering
        if ($request->filled('date_from') && $request->filled('date_to')) {
            // Custom date range
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            // From date only
            $query->where('date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            // To date only
            $query->where('date', '<=', $request->date_to);
        } else {
            // Default: Show only today and future dates for all reservations
            $today = now()->format('Y-m-d');
            $query->where('date', '>=', $today);
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

        // Filter by payment status - default to paid only unless explicitly filtered
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        } else {
            // Default: Show only paid reservations
            $query->where('payment_status', 'paid');
        }

        // Search by booking code, customer name, or vehicle type
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

        $reservations = $query->orderBy('date', 'asc')->orderBy('created_at', 'desc')->get();
        
        // Group reservations by transaction_id to avoid showing duplicate bookings
        $groupedReservations = $this->groupReservationsByTransaction($reservations);
        
        // Get data for edit modals
        $customers = Customer::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        // $packageVariants = PackageVariant::with('package')->where('is_active', true)->get();
        // Replaced with Packages and RiderTypes for new selection logic
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $riderTypes = RiderType::orderBy('id')->get();
        $scheduleSlots = ScheduleSlot::orderBy('sort_order')->get();
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.reservation-dashboard', compact('reservations', 'groupedReservations', 'customers', 'packages', 'riderTypes', 'scheduleSlots', 'vehicleTypes'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $riderTypes = RiderType::orderBy('id')->get();
        $scheduleSlots = ScheduleSlot::orderBy('sort_order')->get();
        $reservations = collect(); // Empty collection for create view
        $groupedReservations = collect(); // Empty collection for create view
        
        return view('admin.reservation-dashboard', compact('customers', 'packages', 'riderTypes', 'scheduleSlots', 'reservations', 'groupedReservations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id', // Replaces package_variant_id
            // 'rider_type_id' => 'required|exists:rider_types,id', // Assuming added to form
            'schedule_slot_id' => 'required|exists:schedule_slots,id',
            'date' => 'required|date|after_or_equal:today',
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

        // Generate unique booking code
        $validated['booking_code'] = $this->generateBookingCode();
        $validated['report_time'] = ScheduleSlot::find($request->schedule_slot_id)->report_time;

        Reservation::create($validated);

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation created successfully.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'package', 'scheduleSlot', 'items', 'payments', 'promoRedemptions.promoCode']);
        $reservations = collect([$reservation]); // Single reservation for show view
        $groupedReservations = $this->groupReservationsByTransaction($reservations);
        
        return view('admin.reservation-dashboard', compact('reservation', 'reservations', 'groupedReservations'));
    }

    public function edit(Reservation $reservation)
    {
        $customers = Customer::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $packages = Package::where('is_active', true)->orderBy('name')->get();
        $riderTypes = RiderType::orderBy('id')->get();
        $scheduleSlots = ScheduleSlot::orderBy('sort_order')->get();
        $reservations = collect([$reservation]); // Single reservation for edit view
        $groupedReservations = $this->groupReservationsByTransaction($reservations);
        
        return view('admin.reservation-dashboard', compact('reservation', 'customers', 'packages', 'riderTypes', 'scheduleSlots', 'reservations', 'groupedReservations'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id', // Replaces package_variant_id
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

        // Store old status for comparison
        $oldBookingStatus = $reservation->booking_status;
        $oldPaymentStatus = $reservation->payment_status;

        $validated['report_time'] = ScheduleSlot::find($request->schedule_slot_id)->report_time;

        $reservation->update($validated);

        // Check if booking status changed and dispatch event
        if ($oldBookingStatus !== $validated['booking_status']) {
            // Load the reservation with relationships for SMS
            $reservationWithRelations = Reservation::with(['customer', 'package'])->find($reservation->id);
            event(new BookingStatusUpdated($reservationWithRelations, $oldBookingStatus, $validated['booking_status'], [
                'updated_by' => 'Admin',
                'update_time' => now()->toDateTimeString(),
            ]));
        }

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation updated successfully.');
    }

    public function destroy(Reservation $reservation)
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
            
            $message = $deletedCount > 1 
                ? "Multi-package booking deleted successfully. {$deletedCount} reservations removed."
                : "Reservation deleted successfully.";
        } else {
            // Single reservation without transaction_id
            $reservation->delete();
            $message = "Reservation deleted successfully.";
        }

        return redirect()->route('admin.reservations.index')
            ->with('success', $message);
    }

    /**
     * Display reservation history for past dates only
     * This shows all reservations (any status) that have already occurred
     */
    public function history(Request $request)
    {
        $query = Reservation::with(['customer', 'package.vehicleTypes', 'scheduleSlot']);

        // Date range filtering for history
        if ($request->filled('date_from') && $request->filled('date_to')) {
            // Custom date range
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            // From date only
            $query->where('date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            // To date only
            $query->where('date', '<=', $request->date_to);
        } else {
            // Default: Show only past dates for history
            $today = now()->format('Y-m-d');
            $query->where('date', '<', $today);
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

        // Filter by payment status - default to paid only unless explicitly filtered
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        } else {
            // Default: Show only paid reservations
            $query->where('payment_status', 'paid');
        }

        // Search by booking code, customer name, or vehicle type
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

        $reservations = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();
        
        // Group reservations by transaction_id to avoid showing duplicate bookings
        $groupedReservations = $this->groupReservationsByTransaction($reservations);
        
        $vehicleTypes = \App\Models\VehicleType::where('is_active', true)->orderBy('name')->get();

        // Debug information
        if ($request->filled('vehicle_type') || $request->filled('search') || $request->filled('booking_status') || $request->filled('payment_status') || $request->filled('date_from') || $request->filled('date_to')) {
            Log::info('Filter applied', [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'vehicle_type' => $request->vehicle_type,
                'search' => $request->search,
                'booking_status' => $request->booking_status,
                'payment_status' => $request->payment_status,
                'total_results' => $reservations->count(),
                'grouped_results' => $groupedReservations->count()
            ]);
        }

        return view('admin.view-reservation-dashboard', compact('groupedReservations', 'vehicleTypes'));
    }

    /**
     * Group reservations by transaction_id to avoid showing duplicate bookings
     * This ensures that multi-package bookings appear as a single booking entry
     */
    private function groupReservationsByTransaction($reservations)
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

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (Reservation::where('booking_code', $code)->exists());

        return $code;
    }

    /**
     * Export current and future reservations to CSV
     */
    public function exportPending(Request $request)
    {
        try {
            $query = Reservation::with(['customer', 'package.vehicleTypes', 'scheduleSlot']);

            // Date range filtering for export
            if ($request->filled('date_from') && $request->filled('date_to')) {
                // Custom date range
                $query->whereBetween('date', [$request->date_from, $request->date_to]);
            } elseif ($request->filled('date_from')) {
                // From date only
                $query->where('date', '>=', $request->date_from);
            } elseif ($request->filled('date_to')) {
                // To date only
                $query->where('date', '<=', $request->date_to);
            } else {
                // Default: Show only today and future dates for all reservations
                $today = now()->format('Y-m-d');
                $query->where('date', '>=', $today);
            }

            // Apply filters
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
            }

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

            $reservations = $query->orderBy('date', 'asc')->orderBy('created_at', 'desc')->get();

            return $this->generateCsv($reservations, 'current_future_reservations_' . now()->format('Y-m-d'));
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
            $query = Reservation::with(['customer', 'package.vehicleTypes', 'scheduleSlot']);

            // Date range filtering for export
            if ($request->filled('date_from') && $request->filled('date_to')) {
                // Custom date range
                $query->whereBetween('date', [$request->date_from, $request->date_to]);
            } elseif ($request->filled('date_from')) {
                // From date only
                $query->where('date', '>=', $request->date_from);
            } elseif ($request->filled('date_to')) {
                // To date only
                $query->where('date', '<=', $request->date_to);
            } else {
                // Default: Show only past dates for history
                $today = now()->format('Y-m-d');
                $query->where('date', '<', $today);
            }

            // Apply filters
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
            }

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

            $reservations = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get();

            return $this->generateCsv($reservations, 'reservation_history_' . now()->format('Y-m-d'));
        } catch (\Exception $e) {
            Log::error('Export reservation history failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to export data. Please try again.');
        }
    }

    /**
     * Generate CSV file from reservations
     */
    private function generateCsv($reservations, $filename)
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
}
