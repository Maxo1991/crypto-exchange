<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderMatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order; 
    public $counterpartyId;

    public function __construct(Order $order, $counterpartyId)
    {
        $this->order = $order;
        $this->counterpartyId = $counterpartyId;
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->order->user_id),
            new PrivateChannel('user.' . $this->counterpartyId),
        ];
    }

    public function broadcastWith()
    {
        return [
            'order' => $this->order,
        ];
    }
}
