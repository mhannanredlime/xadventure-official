<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Events\PaymentConfirmed;
use App\Services\AmarPayService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    private $amarPayService;

    public function __construct(AmarPayService $amarPayService)
    {
        $this->amarPayService = $amarPayService;
    }

    public function index(Request $request)
    {
        // Check if we have a payment_id from session (for credit card payments)
        $paymentId = session()->get('payment_id');
        
        if ($paymentId) {
            $payment = Payment::with(['reservation.customer', 'reservation.packageVariant.package'])->find($paymentId);
            if ($payment) {
                return view('frontend.payment', compact('payment'));
            }
        }
        
        // For check payments, show success message
        $successMessage = session()->get('success');
        $errorMessage = session()->get('error');
        
        return view('frontend.payment', compact('successMessage', 'errorMessage'));
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $reservation = Reservation::findOrFail($validated['reservation_id']);
        
        $result = $this->amarPayService->initiatePayment($reservation, $validated['amount']);
        
        if ($result['success']) {
            return redirect($result['redirect_url']);
        }
        
        return redirect()->back()->with('error', $result['message']);
    }

    public function amarpaySuccess(Request $request)
    {
        Log::info('AmarPay success callback received', [
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Process the callback data
        $processed = $this->amarPayService->processCallback($request->all());
        
        if ($processed) {
            // Get the booking code from the callback data
            $bookingCode = $request->get('opt_b'); // This contains the booking code
            
            Log::info('AmarPay success processing', [
                'booking_code' => $bookingCode,
                'processed' => $processed
            ]);
            
            if ($bookingCode) {
                // Store the booking code in session for the confirmation page
                session(['last_booking_code' => $bookingCode]);
                
                Log::info('Redirecting to booking confirmation', [
                    'booking_code' => $bookingCode,
                    'route' => 'booking.confirmation'
                ]);
                
                return redirect()->route('booking.confirmation', ['booking_code' => $bookingCode])
                    ->with('success', 'Payment completed successfully!');
            } else {
                // Fallback if booking code is not available
                Log::warning('Booking code not found in callback data', [
                    'callback_data' => $request->all()
                ]);
                
                return redirect()->route('booking.confirmation')
                    ->with('success', 'Payment completed successfully!');
            }
        }
        
        return redirect()->route('checkout.index')
            ->with('error', 'Payment verification failed. Please contact support.');
    }

    public function amarpayFail(Request $request)
    {
        Log::info('AmarPay fail callback received', [
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Process the callback data even for failed payments
        $processed = $this->amarPayService->processCallback($request->all());
        
        // Extract error details from callback data
        $errorMessage = 'Payment failed. Please try again.';
        $reason = $request->get('reason', 'Unknown error');
        $pgErrorCode = $request->get('pg_error_code_details', '');
        
        // Create more specific error message based on callback data
        if ($reason && $reason !== 'Not Available') {
            $errorMessage = "Payment failed: {$reason}";
        } elseif ($pgErrorCode && $pgErrorCode !== 'Not Available') {
            $errorMessage = "Payment failed: {$pgErrorCode}";
        }
        
        Log::info('AmarPay fail processing', [
            'processed' => $processed,
            'reason' => $reason,
            'pg_error_code' => $pgErrorCode,
            'error_message' => $errorMessage
        ]);
        
        return redirect()->route('payment.failed')
            ->with('error', $errorMessage)
            ->with('payment_details', [
                'reason' => $reason,
                'pg_error_code' => $pgErrorCode,
                'transaction_id' => $request->get('mer_txnid'),
                'amount' => $request->get('amount'),
                'currency' => $request->get('currency')
            ]);
    }

    public function amarpayCancel(Request $request)
    {
        Log::info('AmarPay cancel callback received', [
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        Log::info('AmarPay cancel processing', [
            'transaction_id' => $request->get('mer_txnid'),
            'amount' => $request->get('amount'),
            'currency' => $request->get('currency')
        ]);
        
        return redirect()->route('payment.failed')
            ->with('error', 'Payment was cancelled by user. You can try again with a different payment method.')
            ->with('payment_details', [
                'reason' => 'Payment cancelled by user',
                'transaction_id' => $request->get('mer_txnid'),
                'amount' => $request->get('amount'),
                'currency' => $request->get('currency')
            ]);
    }

    public function amarpayIPN(Request $request)
    {
        Log::info('AmarPay IPN received', ['data' => $request->all()]);
        
        // Process IPN data
        $processed = $this->amarPayService->processCallback($request->all());
        
        // Return success response to Amar Pay
        return response()->json(['status' => 'success']);
    }

    public function success(Request $request)
    {
        $transactionId = $request->get('tran_id');
        $payment = Payment::with(['reservation.customer', 'reservation.packageVariant.package'])->where('transaction_id', $transactionId)->first();
        
        if ($payment) {
            $reservation = $payment->reservation;
            
            session()->put('last_booking_code', $reservation->booking_code);
            
            // Payment confirmation event is fired by AmarPayService->processCallback()
            
            return redirect()->route('booking.confirmation')
                ->with('success', 'Payment successful! Your booking has been confirmed.');
        }
        
        return redirect()->route('packages.custom.index')->with('error', 'Payment not found.');
    }

    public function fail(Request $request)
    {
        $transactionId = $request->get('tran_id');
        $payment = \App\Models\Payment::where('transaction_id', $transactionId)->first();
        
        if ($payment) {
            $reservation = $payment->reservation;
            return view('frontend.payment.fail', compact('reservation', 'payment'));
        }
        
        return redirect()->route('packages.custom.index')->with('error', 'Payment not found.');
    }

    public function cancel(Request $request)
    {
        $transactionId = $request->get('tran_id');
        $payment = \App\Models\Payment::where('transaction_id', $transactionId)->first();
        
        if ($payment) {
            $reservation = $payment->reservation;
            return view('frontend.payment.cancel', compact('reservation', 'payment'));
        }
        
        return redirect()->route('packages.custom.index')->with('error', 'Payment not found.');
    }

    public function ipn(Request $request)
    {
        $data = $request->all();
        
        $result = $this->amarPayService->processCallback($data);
        
        if ($result) {
            return response('OK', 200);
        }
        
        return response('FAILED', 400);
    }
}
