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

class PaymentStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;
    public string $oldStatus;
    public string $newStatus;
    public array $updateData;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment, string $oldStatus, string $newStatus, array $updateData = [])
    {
        $this->payment = $payment;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->updateData = $updateData;
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

