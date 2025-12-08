<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function index()
    {
        // Use jQuery DataTables in view
        $bookings = Reservation::with(['user', 'reservation_items.package_variant.package'])
            ->latest()
            ->paginate(15);
            
        return view('admin.bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = Reservation::with(['user', 'reservation_items.package_variant.package', 'promo_code'])
            ->findOrFail($id);
            
        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'required|in:unpaid,paid,refunded',
        ]);

        $booking = Reservation::findOrFail($id);
        $booking->update([
            'booking_status' => $request->booking_status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->back()->with('success', 'Booking status updated successfully.');
    }
}
