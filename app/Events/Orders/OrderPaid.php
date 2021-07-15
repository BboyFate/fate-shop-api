<?php

namespace App\Events\Orders;

use Illuminate\Queue\SerializesModels;
use App\Models\Orders\Order;

class OrderPaid
{
    use SerializesModels;

    protected $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
