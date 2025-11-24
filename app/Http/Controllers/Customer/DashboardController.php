<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Get customer's recent reservations (only confirmed/paid)
        $recentReservations = $customer->reservations()
            ->with(['packageVariant.package', 'scheduleSlot', 'payments'])
            ->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->take(10) // Get more to account for grouping
            ->get();

        // Get upcoming reservations (only confirmed/paid)
        $upcomingReservations = $customer->reservations()
            ->with(['packageVariant.package', 'scheduleSlot', 'payments'])
            ->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled')
            ->where('booking_status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->take(10) // Get more to account for grouping
            ->get();

        // Group reservations by transaction_id
        $groupedRecentReservations = $this->groupReservationsByTransaction($recentReservations)->take(5);
        $groupedUpcomingReservations = $this->groupReservationsByTransaction($upcomingReservations)->take(5);

        // Get statistics (count unique transactions, not individual reservations) - only paid
        $allReservations = $customer->reservations()
            ->with('payments')
            ->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled')
            ->get();
        $groupedAllReservations = $this->groupReservationsByTransaction($allReservations);
        
        $totalReservations = $groupedAllReservations->count();
        $completedReservations = $groupedAllReservations->where('booking_status', 'completed')->count();
        $pendingReservations = $groupedAllReservations->where('booking_status', 'pending')->count();
        $confirmedReservations = $groupedAllReservations->where('booking_status', 'confirmed')->count();

        return view('customer.dashboard', compact(
            'customer',
            'groupedRecentReservations',
            'groupedUpcomingReservations',
            'totalReservations',
            'completedReservations',
            'pendingReservations',
            'confirmedReservations'
        ));
    }

    public function profile()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile', compact('customer'));
    }

    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Full name is required.',
            'name.max' => 'Name is too long.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already taken.',
            'phone.max' => 'Phone number is too long.',
            'address.max' => 'Address is too long.',
        ]);

        $customer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('customer.profile')
            ->with('success', 'Profile updated successfully!');
    }

    public function reservations()
    {
        $customer = Auth::guard('customer')->user();
        
        $reservations = $customer->reservations()
            ->with(['packageVariant.package', 'scheduleSlot', 'payments'])
            ->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group reservations by transaction_id
        $groupedReservations = $this->groupReservationsByTransaction($reservations);

        return view('customer.reservations', compact('customer', 'groupedReservations'));
    }

    public function reservationDetails($id)
    {
        $customer = Auth::guard('customer')->user();
        
        $reservation = $customer->reservations()
            ->with(['packageVariant.package', 'scheduleSlot'])
            ->where('payment_status', 'paid')
            ->where('booking_status', '!=', 'cancelled')
            ->findOrFail($id);

        return view('customer.reservation-details', compact('customer', 'reservation'));
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
}
