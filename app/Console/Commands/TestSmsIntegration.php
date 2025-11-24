<?php

namespace App\Console\Commands;

use App\Events\BookingConfirmed;
use App\Events\PaymentConfirmed;
use App\Models\Reservation;
use App\Models\Payment;
use Illuminate\Console\Command;

class TestSmsIntegration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test-integration {--event=booking} {--phone=8801887983638}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS integration with booking system events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testing SMS Integration with Booking System...');
        
        $event = $this->option('event');
        $phone = $this->option('phone');
        
        $this->line("Testing event: {$event}");
        $this->line("Test phone: {$phone}");
        
        switch ($event) {
            case 'booking':
                $this->testBookingConfirmationEvent($phone);
                break;
            case 'payment':
                $this->testPaymentConfirmationEvent($phone);
                break;
            default:
                $this->error("Unknown event: {$event}");
                $this->line("Available events: booking, payment");
                return 1;
        }
        
        $this->info('✅ SMS Integration test completed!');
        return 0;
    }

    /**
     * Test booking confirmation event
     */
    private function testBookingConfirmationEvent(string $phone): void
    {
        $this->info('📋 Testing Booking Confirmation Event...');
        
        // Find a recent reservation or create a test one
        $reservation = Reservation::with(['customer', 'packageVariant.package'])
            ->whereHas('customer', function ($query) use ($phone) {
                $query->where('phone', 'LIKE', '%' . substr($phone, -10));
            })
            ->latest()
            ->first();
            
        if (!$reservation) {
            $this->warn('⚠️  No recent reservation found for testing. Creating a mock event...');
            
            // Create a mock reservation for testing
            $reservation = $this->createMockReservation($phone);
        }
        
        $this->line("Using reservation: {$reservation->booking_code}");
        $this->line("Customer: {$reservation->customer->name} ({$reservation->customer->phone})");
        $this->line("Package: {$reservation->packageVariant->package->name}");
        $this->line("Amount: {$reservation->total_amount} BDT");
        
        // Fire the booking confirmation event
        $this->line('🚀 Firing BookingConfirmed event...');
        event(new BookingConfirmed($reservation, [
            'payment_method' => 'credit_card',
            'transaction_id' => 'TEST_' . time(),
        ]));
        
        $this->line('✅ Booking confirmation event fired successfully!');
        $this->line('📱 Check SMS logs for delivery status.');
    }

    /**
     * Test payment confirmation event
     */
    private function testPaymentConfirmationEvent(string $phone): void
    {
        $this->info('💰 Testing Payment Confirmation Event...');
        
        // Find a recent payment or create a test one
        $payment = Payment::with(['reservation.customer', 'reservation.packageVariant.package'])
            ->whereHas('reservation.customer', function ($query) use ($phone) {
                $query->where('phone', 'LIKE', '%' . substr($phone, -10));
            })
            ->latest()
            ->first();
            
        if (!$payment) {
            $this->warn('⚠️  No recent payment found for testing. Creating a mock event...');
            
            // Create a mock payment for testing
            $payment = $this->createMockPayment($phone);
        }
        
        $this->line("Using payment: {$payment->transaction_id}");
        $this->line("Reservation: {$payment->reservation->booking_code}");
        $this->line("Customer: {$payment->reservation->customer->name} ({$payment->reservation->customer->phone})");
        $this->line("Amount: {$payment->amount} BDT");
        $this->line("Method: {$payment->payment_method}");
        
        // Fire the payment confirmation event
        $this->line('🚀 Firing PaymentConfirmed event...');
        event(new PaymentConfirmed($payment, [
            'transaction_id' => $payment->transaction_id,
            'payment_method' => $payment->payment_method,
        ]));
        
        $this->line('✅ Payment confirmation event fired successfully!');
        $this->line('📱 Check SMS logs for delivery status.');
    }

    /**
     * Create a mock reservation for testing
     */
    private function createMockReservation(string $phone): Reservation
    {
        // Find or create a customer
        $customer = \App\Models\Customer::where('phone', 'LIKE', '%' . substr($phone, -10))->first();
        
        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => $phone,
                'address' => 'Test Address',
            ]);
        }
        
        // Find a package variant
        $variant = \App\Models\PackageVariant::with('package')->first();
        if (!$variant) {
            throw new \Exception('No package variants found in database');
        }
        
        // Find a schedule slot
        $slot = \App\Models\ScheduleSlot::first();
        if (!$slot) {
            throw new \Exception('No schedule slots found in database');
        }
        
        // Create a test reservation
        $reservation = Reservation::create([
            'booking_code' => 'TEST' . strtoupper(substr(md5(time()), 0, 6)),
            'customer_id' => $customer->id,
            'package_variant_id' => $variant->id,
            'schedule_slot_id' => $slot->id,
            'date' => now()->addDays(7),
            'report_time' => $slot->start_time ?? '09:00:00',
            'party_size' => 2,
            'subtotal' => 5000,
            'total_amount' => 5000,
            'booking_status' => 'confirmed',
            'payment_status' => 'completed',
            'notes' => 'Test reservation for SMS integration',
        ]);
        
        return $reservation->load(['customer', 'packageVariant.package']);
    }

    /**
     * Create a mock payment for testing
     */
    private function createMockPayment(string $phone): Payment
    {
        // Create a mock reservation first
        $reservation = $this->createMockReservation($phone);
        
        // Create a test payment
        $payment = Payment::create([
            'customer_id' => $reservation->customer_id,
            'reservation_id' => $reservation->id,
            'method' => 'credit_card',
            'payment_method' => 'credit_card',
            'amount' => $reservation->total_amount,
            'currency' => 'BDT',
            'status' => 'completed',
            'transaction_id' => 'TEST_PAY_' . strtoupper(substr(md5(time()), 0, 8)),
            'paid_at' => now(),
        ]);
        
        return $payment->load(['reservation.customer', 'reservation.packageVariant.package']);
    }
}

