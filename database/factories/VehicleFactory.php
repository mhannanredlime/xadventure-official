<?php

namespace Database\Factories;

use App\Models\VehicleType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_type_id' => VehicleType::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'details' => $this->faker->sentence(),
            'is_active' => true,
            'op_start_date' => $this->faker->date(),
        ];
    }

    /**
     * Indicate that the vehicle is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a vehicle with a specific vehicle type.
     */
    public function forVehicleType(VehicleType $vehicleType): static
    {
        return $this->state(fn (array $attributes) => [
            'vehicle_type_id' => $vehicleType->id,
        ]);
    }
}
