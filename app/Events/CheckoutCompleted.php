<?php

namespace App\Events;

use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CheckoutCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;
    public array $reservations;
    public array $checkoutData;

    /**
     * Create a new event instance.
     */
    public function __construct(Customer $customer, array $reservations, array $checkoutData = [])
    {
        $this->customer = $customer;
        $this->reservations = $reservations;
        $this->checkoutData = $checkoutData;
    }
}

