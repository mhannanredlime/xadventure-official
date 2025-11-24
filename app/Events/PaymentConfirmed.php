<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public array $paymentData;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment, array $paymentData = [])
    {
        $this->payment = $payment;
        $this->paymentData = $paymentData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

