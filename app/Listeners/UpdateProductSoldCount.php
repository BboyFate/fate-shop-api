<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Orders\OrderPaid;
use App\Models\Orders\OrderItem;

class UpdateProductSoldCount implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order = $event->getOrder();
        $order->load('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            // 查询该已支付的订单，对应的商品的数量
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })->sum('qty');

            $product->update(['sold_count' => $soldCount]);
        }
    }
}
