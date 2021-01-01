<?php

use App\Models\SystemImage;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemReview;
use App\Models\Product;

class OrdersSeeder extends Seeder
{
    public function run()
    {
        $faker = app(Faker\Generator::class);
        $orders = factory(Order::class, 100)->create();
        // 被购买的商品，用于后面更新商品销量和评分
        $products = collect([]);
        $image = SystemImage::query()->inRandomOrder()->first();

        $nowAt = $faker->dateTimeBetween('now');

        foreach ($orders as $order) {
            // 每笔订单随机 1 - 3 个商品
            $items = factory(OrderItem::class, random_int(1, 3))->create([
                'user_id'  => $order->user_id,
                'order_id' => $order->id,
            ]);

            foreach ($items as $item) {
                // 已支付的订单里
                if ($order->payment_no) {
                    // 已收货里 80% 添加评论信息
                    if ($order->ship_status == Order::SHIP_STATUS_RECEIVED) {
                        $isReviewed = random_int(0, 9) < 8 ? true : false;
                        if ($isReviewed) {
                            $item->update([
                                'reviewed' => $isReviewed
                            ]);
                            // 添加到评论表
                            factory(OrderItemReview::class)->create([
                                'order_item_id'  => $item->id,
                                'user_id'        => $item->user_id,
                                'product_id'     => $item->product_id,
                                'product_sku_id' => $item->product_sku_id,
                                'rating'         => random_int(1, 5),  // 随机评分 1 - 5
                                'review'         => $faker->sentence,
                                'is_verified'    => random_int(0, 9) > 2 ? true : false,
                                'images'         => [$image->path],
                            ]);
                        }
                        continue;
                    }

                    // 待发货和已收货里 90% 申请退款，已评论（订单完成）的不能退款
                    if (($order->ship_status == Order::SHIP_STATUS_PENDING
                        || $order->ship_status == Order::SHIP_STATUS_DELIVERED) && $item->reviewed == false) {
                        if (random_int(0, 9) < 9) {
                            $item->update([
                                'refunded_money'     => random_int(0, 9) < 5 ? $item->price : $faker->randomFloat(2, 0.01, $item->price),
                                'refund_status'      => OrderItem::REFUND_STATUS_SUCCESS,
                                'refund_no'          => OrderItem::getAvailableRefundNo(),
                                'refunded_at'        => $nowAt,
                                'refund_verified_at' => $nowAt,
                                'is_applied_refund'  => true,
                            ]);
                        }
                    }
                }
            }

            // 计算总价
            $total = $items->sum(function (OrderItem $item) {
                return $item->price * $item->amount;
            });

            // 更新订单总价
            $order->update([
                'total_amount' => $total,
            ]);

            // 将这笔订单的商品合并到商品集合中
            $products = $products->merge($items->pluck('product'));
        }

        // 根据商品 ID 过滤掉重复的商品
        $products->unique('id')->each(function (Product $product) {
            // 查出该商品的销量、评分、评价数
            $result = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    \DB::raw('sum(amount) as sold_count'),
                ]);

            $reviewRes = OrderItemReview::query()
                ->where('product_id', $product->id)
                ->first([
                    \DB::raw('count(*) as review_count'),
                    \DB::raw('avg(rating) as rating'),
                ]);

            $product->update([
                'rating'       => $reviewRes->rating ?: 5, // 如果某个商品没有评分，则默认为 5 分
                'review_count' => $reviewRes->review_count,
                'sold_count'   => $result->sold_count,
            ]);
        });
    }
}
