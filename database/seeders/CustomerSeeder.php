<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding customers...');

        try {
            $faker = Faker::create();

            // Create sample customers with realistic data
            $customers = [
                [
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '+1-555-0101',
                    'address' => '123 Main Street, City, State 12345',
                ],
                [
                    'name' => 'Sarah Johnson',
                    'email' => 'sarah.johnson@example.com',
                    'phone' => '+1-555-0201',
                    'address' => '456 Oak Avenue, Town, State 67890',
                ],
                [
                    'name' => 'Michael Brown',
                    'email' => 'michael.brown@example.com',
                    'phone' => '+1-555-0301',
                    'address' => '789 Pine Road, Village, State 11111',
                ],
                [
                    'name' => 'Emily Davis',
                    'email' => 'emily.davis@example.com',
                    'phone' => '+1-555-0401',
                    'address' => '321 Elm Street, Borough, State 22222',
                ],
                [
                    'name' => 'David Wilson',
                    'email' => 'david.wilson@example.com',
                    'phone' => '+1-555-0501',
                    'address' => '654 Maple Drive, District, State 33333',
                ],
            ];

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($customers as $customerData) {
                $customer = Customer::updateOrCreate(
                    ['email' => $customerData['email']],
                    $customerData
                );

                if ($customer->wasRecentlyCreated) {
                    $createdCount++;
                    
                    // Create associated user account for the customer
                    User::updateOrCreate(
                        ['email' => $customerData['email']],
                        [
                            'name' => $customerData['name'],
                            'email' => $customerData['email'],
                            'password' => Hash::make('password'),
                            'email_verified_at' => now(),
                            'is_admin' => false,
                        ]
                    );
                } else {
                    $updatedCount++;
                }
            }

            // Create additional random customers for testing
            $additionalCustomers = 10;
            for ($i = 0; $i < $additionalCustomers; $i++) {
                $firstName = $faker->firstName();
                $lastName = $faker->lastName();
                $email = strtolower($firstName . '.' . $lastName . '@example.com');

                $customer = Customer::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $firstName . ' ' . $lastName,
                        'email' => $email,
                        'phone' => $faker->phoneNumber(),
                        'address' => $faker->address(),
                    ]
                );

                if ($customer->wasRecentlyCreated) {
                    $createdCount++;
                    
                    // Create associated user account
                    User::updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => $firstName . ' ' . $lastName,
                            'email' => $email,
                            'password' => Hash::make('password'),
                            'email_verified_at' => now(),
                            'is_admin' => false,
                        ]
                    );
                } else {
                    $updatedCount++;
                }
            }

            $this->command->info("Customer seeding completed: {$createdCount} created, {$updatedCount} updated.");

        } catch (\Exception $e) {
            $this->command->error('Error seeding customers: ' . $e->getMessage());
            Log::error('Customer seeding failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}
