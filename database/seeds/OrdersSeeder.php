<?php

use App\Models\SystemImage;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemReview;
use App\Models\OrderItemShipment;
use App\Models\OrderItemRefund;
use App\Models\Product;

class OrdersSeeder extends Seeder
{
    public function run()
    {
        $faker = app(Faker\Generator::class);
        $orders = factory(Order::class, 50)->create();

        // 被购买的商品，用于后面更新商品销量和评分
        $products = collect([]);
        $image = SystemImage::query()->inRandomOrder()->first();

        $nowAt = $faker->dateTimeBetween('now');

        foreach ($orders as $order) {
            $shipmentData = [
                'order_id'           => $order->id,
                'express_company_id' => 1,
                'readied_at'         => $faker->dateTimeBetween('-30 days', '-25 days'),  // 备货时间
                'delivered_at'       => $faker->dateTimeBetween('-24 days', '-20 days'),  // 发货时间
                'received_at'        => $faker->dateTimeBetween('-19 days', '-10 days'),  // 收货时间
            ];
            switch ($order->shipment_state) {
                // 未处理
                case Order::SHIPMENT_STATE_PENDING:
                    $shipmentData['readied_at'] = null;
                    $shipmentData['delivered_at'] = null;
                    $shipmentData['received_at'] = null;
                    break;
                // 备货
                case Order::SHIPMENT_STATE_READY:
                    $shipmentData['shipment_state'] = OrderItemShipment::SHIPMENT_STATE_READY;
                    $shipmentData['delivered_at'] = null;
                    $shipmentData['received_at'] = null;
                    break;
                // 发货
                case Order::SHIPMENT_STATE_FULL_DELIVERED:
                case Order::SHIPMENT_STATE_PARTIALLY_DELIVERED:
                    $shipmentData['shipment_state'] = OrderItemShipment::SHIPMENT_STATE_DELIVERED;
                    $shipmentData['received_at'] = null;
                    break;
                // 已收货
                case Order::SHIPMENT_STATE_FUll_RECEIVED:
                    $shipmentData['shipment_state'] = OrderItemShipment::SHIPMENT_STATE_RECEIVED;
                    break;
                // 已退货
                case Order::SHIPMENT_STATE_FUll_REFUNDED:
                case Order::SHIPMENT_STATE_PARTIALLY_REFUNDED:
                    $shipmentData['shipment_state'] = OrderItemShipment::SHIPMENT_STATE_CANCELED;
                    $shipmentData['canceled_at'] = $faker->dateTimeBetween('-9 days');
                    break;
            }

            $shipment = factory(OrderItemShipment::class)->create($shipmentData);

            // 每笔订单随机 1 - 3 个商品
            $items = factory(OrderItem::class, random_int(1, 3))->create([
                'user_id'     => $order->user_id,
                'order_id'    => $order->id,
            ]);

            foreach ($items as $item) {
                // 已支付的订单
                if ($order->payment_state == Order::PAYMENT_STATE_PAID) {
                    // 物流表状态为：除了 待发货 和 正在备货 的 其他发货状态
                    if (! in_array($shipment->shipment_state, [OrderItemShipment::SHIPMENT_STATE_PENDING, OrderItemShipment::SHIPMENT_STATE_READY])) {
                        $isReviewed = false;
                        $itemUpdateData = [];
                        $itemUpdateData['shipment_id'] = $shipment->id; // 发货才关联 物流表

                        // 物流表状态为 已收货
                        if (in_array($shipment->shipment_state, [OrderItemShipment::SHIPMENT_STATE_RECEIVED])) {
                            // 添加到评论表
                            $isReviewed = random_int(0, 9) < 2 ? true : false;
                            if ($isReviewed) {
                                $itemUpdateData['is_reviewed'] = $isReviewed;

                                $isVerified = random_int(0, 9) > 5 ? true : false;
                                factory(OrderItemReview::class)->create([
                                    'order_item_id'  => $item->id,
                                    'user_id'        => $item->user_id,
                                    'product_id'     => $item->product_id,
                                    'product_sku_id' => $item->product_sku_id,
                                    'rating'         => random_int(1, 5),  // 随机评分 1 - 5
                                    'review'         => $faker->sentence,
                                    'is_verified'    => $isVerified,
                                    'verified_at'    => $isVerified ? $nowAt : null,
                                ]);
                            }
                        }

                        // 未评价的 70% 申请退款
                        if ($isReviewed == false && random_int(0, 9) < 7) {
                            $refund = $item->refund()->make([
                                'refund_no'          => OrderItemRefund::getAvailableRefundNo(),
                                'refund_state'       => OrderItemRefund::REFUND_STATE_SUCCEED,
                                'type'               => OrderItemRefund::TYPE_ONLY_REFUND,
                                'refunded_qty'       => $item->qty,
                                // 'refunded_total'     => random_int(0, 9) < 5 ? $item->price : $faker->randomFloat(2, 0.01, $item->price),
                                'refunded_total'     => $item->price_total + $item->adjustment_total,
                                'refunded_at'        => $nowAt,
                                'refund_verified_at' => $nowAt,
                                'is_verified'        => true,
                            ]);
                            $refund->user()->associate($order->user_id);
                            $refund->order()->associate($order->id);
                            $refund->save();

                            $itemUpdateData['is_applied_refund'] = true;
                        }

                        $item->update($itemUpdateData);
                    }
                }
            }

            // 计算总价
            $total = $items->sum(function (OrderItem $item) {
                return $item->price * $item->qty;
            });
            // 计算优惠价
            $adjustmentTotal = $items->sum(function (OrderItem $item) {
                return $item->adjustment_total;
            });
            // 计算总共多少件商品
            $qtyItem = $items->sum(function (OrderItem $item) {
                return $item->qty;
            });
            $updateOrderData = [
                'qty_item'       => $qtyItem,
                'original_total' => $total,
                'payment_total'  => $total + $adjustmentTotal + $order->adjustment_total,
            ];

            // 所有 order_items 评论后订单设置已完成
            $isCompleted = $items->search(function (OrderItem $item) {
                return $item->is_reviewed == true;
            }, true);
            if ($isCompleted) {
                $updateOrderData['order_state'] = Order::ORDER_STATE_COMPLETED;
            }

            // 更新订单
            $order->update($updateOrderData);

            // 将这笔订单的商品合并到商品集合中
            $products = $products->merge($items->pluck('product'));
        }

        // 根据商品 ID 过滤掉重复的商品
        $products->unique('id')->each(function (Product $product) {
            // 查出该商品的销量、评分、评价数
            $result = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->where('order_state', Order::ORDER_STATE_COMPLETED);
                })
                ->first([
                    \DB::raw('sum(qty) AS sold_count'),
                ]);

            $reviewRes = OrderItemReview::query()
                ->where('product_id', $product->id)
                ->first([
                    \DB::raw('count(*) AS review_count'),
                    \DB::raw('avg(rating) AS rating'),
                ]);

            $product->update([
                'rating'       => $reviewRes->rating ?: 5, // 如果某个商品没有评分，则默认为 5 分
                'review_count' => $reviewRes->review_count,
                'sold_count'   => $result->sold_count ?: 0,
            ]);
        });
    }
}
