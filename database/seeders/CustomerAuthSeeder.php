<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerAuthSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test customer account
        Customer::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1234567890',
                'address' => '123 Test Street, Test City, TC 12345',
                'email_verified_at' => now(),
            ]
        );

        // Create a few more test customers
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1987654321',
                'address' => '456 Main Ave, City Center, CC 54321',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'phone' => '+1555123456',
                'address' => '789 Oak Blvd, Downtown, DT 67890',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::updateOrCreate(
                ['email' => $customerData['email']],
                $customerData
            );
        }
    }
}
