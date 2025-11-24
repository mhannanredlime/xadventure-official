<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Customer;
use App\Models\PackageVariant;
use App\Models\ScheduleSlot;
use App\Models\Availability;
use App\Models\Payment;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding reservations...');

        try {
            $faker = Faker::create();

            // Get required data
            $customers = Customer::all();
            $variants = PackageVariant::where('is_active', true)->get();
            $slots = ScheduleSlot::where('is_active', true)->get();
            $promoCodes = PromoCode::where('status', 'active')->get();

            if ($customers->isEmpty() || $variants->isEmpty() || $slots->isEmpty()) {
                $this->command->warn('Missing required data for reservations. Skipping reservation seeding.');
                return;
            }

            $reservations = [
                [
                    'customer_id' => $customers->first()->id,
                    'booking_status' => 'confirmed',
                    'payment_status' => 'paid',
                    'total_amount' => 1500.00,
                    'discount_amount' => 150.00,
                    'subtotal' => 1500.00,
                    'tax_amount' => 0.00,
                    'deposit_amount' => 1500.00,
                    'balance_amount' => 0.00,
                    'notes' => 'First-time customer, very excited about the adventure!',
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(3),
                ],
                [
                    'customer_id' => $customers->skip(1)->first()->id,
                    'booking_status' => 'pending',
                    'payment_status' => 'pending',
                    'total_amount' => 1200.00,
                    'discount_amount' => 0.00,
                    'subtotal' => 1200.00,
                    'tax_amount' => 0.00,
                    'deposit_amount' => 0.00,
                    'balance_amount' => 1200.00,
                    'notes' => 'Weekend booking for family outing',
                    'created_at' => Carbon::now()->subDays(2),
                    'updated_at' => Carbon::now()->subDays(1),
                ],
                [
                    'customer_id' => $customers->skip(2)->first()->id,
                    'booking_status' => 'cancelled',
                    'payment_status' => 'refunded',
                    'total_amount' => 1800.00,
                    'discount_amount' => 180.00,
                    'subtotal' => 1800.00,
                    'tax_amount' => 0.00,
                    'deposit_amount' => 0.00,
                    'balance_amount' => 0.00,
                    'notes' => 'Cancelled due to weather conditions',
                    'created_at' => Carbon::now()->subDays(10),
                    'updated_at' => Carbon::now()->subDays(8),
                ],
                [
                    'customer_id' => $customers->skip(3)->first()->id,
                    'booking_status' => 'completed',
                    'payment_status' => 'paid',
                    'total_amount' => 2000.00,
                    'discount_amount' => 200.00,
                    'subtotal' => 2000.00,
                    'tax_amount' => 0.00,
                    'deposit_amount' => 2000.00,
                    'balance_amount' => 0.00,
                    'notes' => 'Excellent experience, customer was very satisfied',
                    'created_at' => Carbon::now()->subDays(15),
                    'updated_at' => Carbon::now()->subDays(12),
                ],
                [
                    'customer_id' => $customers->skip(4)->first()->id,
                    'booking_status' => 'confirmed',
                    'payment_status' => 'paid',
                    'total_amount' => 800.00,
                    'discount_amount' => 0.00,
                    'subtotal' => 800.00,
                    'tax_amount' => 0.00,
                    'deposit_amount' => 800.00,
                    'balance_amount' => 0.00,
                    'notes' => 'Regular customer, prefers morning slots',
                    'created_at' => Carbon::now()->subDays(1),
                    'updated_at' => Carbon::now(),
                ],
            ];

            $createdReservations = 0;
            $updatedReservations = 0;

            foreach ($reservations as $reservationData) {
                // Add required fields for reservation
                $reservationData['booking_code'] = 'BK' . date('Ymd') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                $reservationData['package_variant_id'] = $variants->random()->id;
                $reservationData['schedule_slot_id'] = $slots->random()->id;
                $reservationData['date'] = Carbon::now()->addDays(rand(1, 30))->format('Y-m-d');
                $reservationData['report_time'] = '08:00:00';
                $reservationData['party_size'] = rand(1, 4);

                $reservation = Reservation::updateOrCreate(
                    [
                        'customer_id' => $reservationData['customer_id'],
                        'created_at' => $reservationData['created_at']
                    ],
                    $reservationData
                );

                if ($reservation->wasRecentlyCreated) {
                    $createdReservations++;
                    
                    // Create reservation items
                    $this->createReservationItems($reservation, $variants, $slots, $faker);
                    
                    // Create payment record
                    $this->createPaymentRecord($reservation, $faker);
                    
                    // Note: Promo code relationship not implemented in reservations table
                    // Promo codes are handled separately through promo_redemptions table
                } else {
                    $updatedReservations++;
                }
            }

            // Create additional random reservations
            $additionalReservations = 8;
            for ($i = 0; $i < $additionalReservations; $i++) {
                $customer = $customers->random();
                $variant = $variants->random();
                $slot = $slots->random();
                
                // Calculate amounts
                $basePrice = $variant->prices->where('price_type', 'weekday')->first()->amount ?? 1000.00;
                $totalAmount = $basePrice;
                $discountAmount = 0;
                
                // Apply random discount
                if (rand(1, 3) === 1 && !$promoCodes->isEmpty()) {
                    $promoCode = $promoCodes->random();
                    $discountAmount = $totalAmount * ($promoCode->discount_value / 100);
                    $discountAmount = min($discountAmount, $promoCode->max_discount);
                }
                
                $finalAmount = $totalAmount - $discountAmount;
                
                $bookingStatus = $faker->randomElement(['confirmed', 'pending', 'completed', 'cancelled']);
                $paymentStatus = match($bookingStatus) {
                    'confirmed', 'completed' => 'paid',
                    'pending' => 'pending',
                    'cancelled' => 'refunded',
                    default => 'pending'
                };

                $reservation = Reservation::create([
                    'customer_id' => $customer->id,
                    'booking_code' => 'BK' . date('Ymd') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                    'package_variant_id' => $variant->id,
                    'schedule_slot_id' => $slot->id,
                    'date' => Carbon::now()->addDays(rand(1, 30))->format('Y-m-d'),
                    'report_time' => '08:00:00',
                    'party_size' => rand(1, 4),
                    'booking_status' => $bookingStatus,
                    'payment_status' => $paymentStatus,
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $totalAmount,
                    'tax_amount' => 0.00,
                    'deposit_amount' => $paymentStatus === 'paid' ? $finalAmount : 0.00,
                    'balance_amount' => $paymentStatus === 'paid' ? 0.00 : $finalAmount,
                    'notes' => $faker->optional(0.7)->sentence(),
                    'created_at' => Carbon::now()->subDays(rand(1, 20)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 19)),
                ]);

                $createdReservations++;
                
                // Create reservation items
                $this->createReservationItems($reservation, collect([$variant]), collect([$slot]), $faker);
                
                // Create payment record
                $this->createPaymentRecord($reservation, $faker);
            }

            $this->command->info("Reservation seeding completed: {$createdReservations} created, {$updatedReservations} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding reservations: ' . $e->getMessage());
            Log::error('Reservation seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    private function createReservationItems($reservation, $variants, $slots, $faker)
    {
        $variant = $variants->random();
        $slot = $slots->random();
        $date = Carbon::now()->addDays(rand(1, 30));
        
        // Create availability if it doesn't exist
        $availability = Availability::firstOrCreate(
            [
                'date' => $date->format('Y-m-d'),
                'package_variant_id' => $variant->id,
                'schedule_slot_id' => $slot->id,
            ],
            [
                'capacity_total' => 10,
                'capacity_reserved' => 0,
                'is_day_off' => false,
            ]
        );

        // Update capacity reserved
        $availability->increment('capacity_reserved');

        ReservationItem::create([
            'reservation_id' => $reservation->id,
            'package_variant_id' => $variant->id,
            'qty' => rand(1, 3),
            'unit_price' => $variant->prices->where('price_type', 'weekday')->first()->amount ?? 1000.00,
            'line_total' => $reservation->total_amount,
        ]);
    }

    private function createPaymentRecord($reservation, $faker)
    {
        $paymentStatus = match($reservation->booking_status) {
            'confirmed', 'completed' => 'paid',
            'pending' => 'pending',
            'cancelled' => 'refunded',
            default => 'pending'
        };

        $paymentStatus = match($paymentStatus) {
            'paid' => 'completed',
            'pending' => 'pending',
            'refunded' => 'refunded',
            default => 'pending'
        };

        Payment::create([
            'reservation_id' => $reservation->id,
            'amount' => $reservation->total_amount - $reservation->discount_amount,
            'method' => $faker->randomElement(['credit_card', 'debit_card', 'cash', 'online']),
            'status' => $paymentStatus,
            'transaction_id' => $faker->uuid(),
            'paid_at' => $reservation->booking_status === 'cancelled' ? null : $reservation->created_at,
        ]);
    }
}
