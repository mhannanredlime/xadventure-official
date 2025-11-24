<?php

namespace Database\Factories;

use App\Models\SmsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmsLog>
 */
class SmsLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_number' => $this->faker->phoneNumber(),
            'message' => $this->faker->sentence(10),
            'template_name' => $this->faker->randomElement([
                'booking_confirmation',
                'payment_confirmation',
                'booking_reminder',
                'booking_cancelled',
                'admin_new_booking'
            ]),
            'provider' => $this->faker->randomElement(['mim', 'twilio']),
            'status' => $this->faker->randomElement(SmsLog::getStatuses()),
            'message_id' => $this->faker->uuid(),
            'error_message' => null,
            'sent_at' => $this->faker->optional()->dateTimeBetween('-1 day', 'now'),
            'delivered_at' => $this->faker->optional()->dateTimeBetween('-1 day', 'now'),
            'metadata' => [
                'cost' => $this->faker->randomFloat(2, 0.01, 2.00),
                'retry_count' => $this->faker->numberBetween(0, 3),
                'priority' => $this->faker->randomElement(['low', 'normal', 'high']),
            ],
        ];
    }

    /**
     * Indicate that the SMS is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SmsLog::STATUS_PENDING,
            'sent_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the SMS is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SmsLog::STATUS_SENT,
            'sent_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the SMS is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SmsLog::STATUS_DELIVERED,
            'sent_at' => $this->faker->dateTimeBetween('-1 hour', '-30 minutes'),
            'delivered_at' => $this->faker->dateTimeBetween('-30 minutes', 'now'),
        ]);
    }

    /**
     * Indicate that the SMS failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SmsLog::STATUS_FAILED,
            'error_message' => $this->faker->randomElement([
                'Invalid phone number',
                'Insufficient balance',
                'Network error',
                'API timeout',
                'Rate limit exceeded'
            ]),
            'sent_at' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Indicate that the SMS is from MiM provider.
     */
    public function mim(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'mim',
        ]);
    }

    /**
     * Indicate that the SMS is a booking confirmation.
     */
    public function bookingConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_name' => 'booking_confirmation',
            'message' => 'Your booking #BK123456 is confirmed for 2025-01-20 at 10:00 AM. Total: 5000 BDT',
        ]);
    }

    /**
     * Indicate that the SMS is a payment confirmation.
     */
    public function paymentConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_name' => 'payment_confirmation',
            'message' => 'Payment received for booking #BK123456. Thank you!',
        ]);
    }
}
