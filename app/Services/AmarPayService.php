<?php

namespace App\Services;

use App\Events\PaymentConfirmed;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmarPayService
{
    private $config;

    public function __construct()
    {
        $this->config = config('services.amarpay');
    }

    public function initiatePayment(Reservation $reservation, float $amount): array
    {
        // Generate unique transaction ID (max 32 characters)
        $tranId = 'ATV_' . $reservation->booking_code . '_' . time();
        
        $payload = [
            'store_id' => $this->config['store_id'],
            'signature_key' => $this->config['signature_key'],
            'tran_id' => $tranId,
            'amount' => number_format($amount, 2, '.', ''),
            'currency' => 'BDT',
            'desc' => 'ATV/UTV Adventure Tour Booking - ' . $reservation->packageVariant->package->name,
            'cus_name' => $reservation->customer->name,
            'cus_email' => $reservation->customer->email,
            'cus_phone' => $reservation->customer->phone,
            'success_url' => url($this->config['success_url']),
            'fail_url' => url($this->config['fail_url']),
            'cancel_url' => url($this->config['cancel_url']),
            'type' => 'json',
            'cus_add1' => $reservation->customer->address ?? 'Dhaka',
            'cus_city' => 'Dhaka',
            'cus_state' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'opt_a' => $reservation->id, // Store reservation ID in optional field
            'opt_b' => $reservation->booking_code, // Store booking code
        ];

        Log::info('AmarPay payment initiation request', [
            'reservation_id' => $reservation->id,
            'tran_id' => $tranId,
            'amount' => $amount,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->config['api_url'], $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            Log::info('AmarPay API response', [
                'response' => $data,
            ]);
            
            if (isset($data['result']) && $data['result'] === 'true' && isset($data['payment_url'])) {
                // Payment record is already created in BookingController
                // Just return the redirect URL
                return [
                    'success' => true,
                    'redirect_url' => $data['payment_url'],
                    'transaction_id' => $tranId,
                ];
            }
        }

        Log::error('AmarPay payment initiation failed', [
            'reservation_id' => $reservation->id,
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return [
            'success' => false,
            'message' => 'Payment initiation failed. Please try again.',
        ];
    }

    public function processCallback(array $data): bool
    {
        Log::info('AmarPay callback received', ['data' => $data]);

        $transactionId = $data['mer_txnid'] ?? $data['tran_id'] ?? null;
        $statusCode = $data['status_code'] ?? null;
        $payStatus = $data['pay_status'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$transactionId) {
            Log::error('AmarPay callback: No transaction ID found', ['data' => $data]);
            return false;
        }

        // Try to find payment by transaction_id first
        $payment = Payment::where('transaction_id', $transactionId)->first();
        
        // If not found, try to find by amount and recent creation (fallback)
        if (!$payment) {
            $payment = Payment::where('method', 'amarpay')
                             ->where('status', 'pending')
                             ->where('amount', $amount)
                             ->where('created_at', '>=', now()->subMinutes(30)) // Within last 30 minutes
                             ->orderBy('created_at', 'desc')
                             ->first();
        }
        
        // If still not found, try to find by reservation amount match
        if (!$payment) {
            $payment = Payment::where('method', 'amarpay')
                             ->where('status', 'pending')
                             ->whereHas('reservation', function($query) use ($amount) {
                                 return $query->where('total_amount', $amount);
                             })
                             ->where('created_at', '>=', now()->subMinutes(30))
                             ->orderBy('created_at', 'desc')
                             ->first();
        }
        
        if (!$payment) {
            Log::error('AmarPay callback: Payment not found', [
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'available_payments' => Payment::where('method', 'amarpay')->where('status', 'pending')->pluck('transaction_id', 'id')
            ]);
            return false;
        }

        // Prevent duplicate processing
        if ($payment->status === 'completed') {
            Log::info('AmarPay callback: Payment already processed', ['transaction_id' => $transactionId]);
            return true;
        }

        // Check payment status based on status_code
        // 0 = initiated, 2 = successful, 3 = expired, 7 = failed
        if ($statusCode == '2' && $payStatus === 'Successful') {
            $payment->update([
                'transaction_id' => $transactionId, // Update with actual AmarPay transaction ID
                'status' => 'completed',
                'paid_at' => now(),
                'payment_details' => array_merge($payment->payment_details ?? [], $data),
            ]);

            // Find all reservations that were created in the same checkout session
            // Since we don't have payment_id in reservations, we'll find by customer and recent creation time
            $primaryReservation = $payment->reservation;
            $allReservations = \App\Models\Reservation::where('customer_id', $primaryReservation->customer_id)
                ->where('created_at', '>=', $payment->created_at->subMinutes(5)) // Within 5 minutes of payment creation
                ->where('payment_status', 'pending')
                ->get();

            // Update all reservations in this checkout session
            foreach ($allReservations as $reservation) {
                $reservation->update([
                    'payment_status' => 'paid',
                    'booking_status' => 'confirmed',
                    'deposit_amount' => $reservation->total_amount, // Individual reservation amount as deposit
                    'balance_amount' => 0, // No remaining balance since full amount paid
                ]);
                
                Log::info('Updated reservation payment status', [
                    'reservation_id' => $reservation->id,
                    'booking_code' => $reservation->booking_code,
                    'payment_id' => $payment->id,
                    'amount' => $reservation->total_amount
                ]);
            }

            // Fire payment confirmation event for SMS notification
            $paymentWithRelations = Payment::with(['reservation.customer', 'reservation.packageVariant.package'])->find($payment->id);
            
            Log::info('Firing PaymentConfirmed event', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reservations_count' => $allReservations->count()
            ]);
            
            event(new PaymentConfirmed($paymentWithRelations, [
                'transaction_id' => $transactionId,
                'payment_method' => 'amarpay',
                'amount' => $amount,
            ]));

            Log::info('AmarPay payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reservations_updated' => $allReservations->count(),
                'status_code' => $statusCode,
            ]);

            return true;
        } else {
            $payment->update([
                'status' => 'failed',
                'payment_details' => array_merge($payment->payment_details ?? [], $data),
            ]);

            // Find all reservations that were created in the same checkout session
            $primaryReservation = $payment->reservation;
            $allReservations = \App\Models\Reservation::where('customer_id', $primaryReservation->customer_id)
                ->where('created_at', '>=', $payment->created_at->subMinutes(5)) // Within 5 minutes of payment creation
                ->where('payment_status', 'pending')
                ->get();

            // Update all reservations to pending status
            foreach ($allReservations as $reservation) {
                $reservation->update([
                    'payment_status' => 'pending',
                    'booking_status' => 'pending',
                ]);
            }

            Log::warning('AmarPay payment failed', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'status_code' => $statusCode,
                'pay_status' => $payStatus,
                'reservations_affected' => $allReservations->count(),
            ]);

            return false;
        }
    }

    public function searchTransaction(string $transactionId): array
    {
        $payload = [
            'store_id' => $this->config['store_id'],
            'signature_key' => $this->config['signature_key'],
            'tran_id' => $transactionId,
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->config['api_url'], $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('AmarPay transaction search failed', [
            'transaction_id' => $transactionId,
            'response' => $response->body(),
        ]);

        return [];
    }
}
