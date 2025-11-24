<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleType>
 */
class VehicleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['ATV', 'UTV', 'Dirt Bike', 'Snowmobile', 'Mountain Bike']),
            'subtitle' => $this->faker->sentence(3),
            'seating_capacity' => $this->faker->numberBetween(1, 4),
            'license_requirement' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the vehicle type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
