<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use App\Events\OrderItemReviewed;
use App\Models\OrderItemReview;

class UpdateProductRating implements ShouldQueue
{
    /**
     * 更新商品评分
     *
     * @param OrderItemReviewed $event
     */
    public function handle(OrderItemReviewed $event)
    {
        $item = $event->getOrderItem()->with(['product'])->first();

        $result = OrderItemReview::query()
            ->where('product_id', $item->product_id)
            ->first([
                DB::raw('count(*) AS review_count'),
                DB::raw('avg(rating) AS rating')
            ]);

        // 更新商品的评分和评价数
        $item->product->update([
            'rating'       => $result->rating,
            'review_count' => $result->review_count,
        ]);
    }
}
