<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use App\Models\OrderItem;

class OrderItemReviewed
{
    use SerializesModels;

    protected $orderItem;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OrderItem $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function getOrderItem()
    {
        return $this->orderItem;
    }
}
