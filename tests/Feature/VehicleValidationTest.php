<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\VehicleType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class VehicleValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $vehicleType;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user
        $this->admin = User::factory()->create(['is_admin' => true]);
        
        // Create a vehicle type
        $this->vehicleType = VehicleType::factory()->create([
            'name' => 'ATV',
            'is_active' => true
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_vehicle_names()
    {
        // Create first vehicle
        $firstVehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->vehicleType->id,
            'name' => 'Test Vehicle',
            'is_active' => true
        ]);

        // Try to create second vehicle with same name
        $response = $this->actingAs($this->admin)
            ->post(route('admin.vehicles.store'), [
                'vehicle_type_id' => $this->vehicleType->id,
                'name' => 'Test Vehicle', // Same name
                'details' => 'Test details',
                'is_active' => true
            ]);

        // Should fail validation
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrors(['name' => 'A vehicle with this name already exists.']);
        
        // Verify only one vehicle exists
        $this->assertEquals(1, Vehicle::where('name', 'Test Vehicle')->count());
    }

    /** @test */
    public function it_allows_updating_vehicle_with_same_name()
    {
        // Create a vehicle
        $vehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->vehicleType->id,
            'name' => 'Test Vehicle',
            'is_active' => true
        ]);

        // Update the same vehicle with same name (should work)
        $response = $this->actingAs($this->admin)
            ->put(route('admin.vehicles.update', $vehicle), [
                'vehicle_type_id' => $this->vehicleType->id,
                'name' => 'Test Vehicle', // Same name
                'details' => 'Updated details',
                'is_active' => true
            ]);

        // Should succeed
        $response->assertRedirect(route('admin.vehical-management'));
        $response->assertSessionHas('success');
        
        // Verify vehicle was updated
        $vehicle->refresh();
        $this->assertEquals('Updated details', $vehicle->details);
    }

    /** @test */
    public function it_prevents_updating_vehicle_to_existing_name()
    {
        // Create two vehicles
        $firstVehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->vehicleType->id,
            'name' => 'First Vehicle',
            'is_active' => true
        ]);

        $secondVehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->vehicleType->id,
            'name' => 'Second Vehicle',
            'is_active' => true
        ]);

        // Try to update second vehicle to first vehicle's name
        $response = $this->actingAs($this->admin)
            ->put(route('admin.vehicles.update', $secondVehicle), [
                'vehicle_type_id' => $this->vehicleType->id,
                'name' => 'First Vehicle', // Existing name
                'details' => 'Updated details',
                'is_active' => true
            ]);

        // Should fail validation
        $response->assertSessionHasErrors(['name']);
        $response->assertSessionHasErrors(['name' => 'A vehicle with this name already exists.']);
        
        // Verify vehicle names remain unchanged
        $firstVehicle->refresh();
        $secondVehicle->refresh();
        $this->assertEquals('First Vehicle', $firstVehicle->name);
        $this->assertEquals('Second Vehicle', $secondVehicle->name);
    }

    /** @test */
    public function it_allows_different_vehicle_names()
    {
        // Create first vehicle
        $firstVehicle = Vehicle::factory()->create([
            'vehicle_type_id' => $this->vehicleType->id,
            'name' => 'First Vehicle',
            'is_active' => true
        ]);

        // Create second vehicle with different name
        $response = $this->actingAs($this->admin)
            ->post(route('admin.vehicles.store'), [
                'vehicle_type_id' => $this->vehicleType->id,
                'name' => 'Second Vehicle', // Different name
                'details' => 'Test details',
                'is_active' => true
            ]);

        // Should succeed
        $response->assertRedirect(route('admin.vehical-management'));
        $response->assertSessionHas('success');
        
        // Verify both vehicles exist
        $this->assertEquals(2, Vehicle::count());
        $this->assertTrue(Vehicle::where('name', 'First Vehicle')->exists());
        $this->assertTrue(Vehicle::where('name', 'Second Vehicle')->exists());
    }
}
