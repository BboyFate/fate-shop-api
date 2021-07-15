<?php

use Illuminate\Database\Seeder;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderItemUnit;
use App\Models\Expresses\ExpressFee;
use App\Models\Orders\OrderAdjustment;
use App\Models\Orders\OrderItemReview;
use App\Models\Orders\OrderShipment;
use App\Models\Orders\OrderItemRefund;
use App\Models\Products\Product;

class OrdersSeeder extends Seeder
{
    public function run()
    {
        $faker = app(Faker\Generator::class);
        $orders = factory(Order::class, 50)->create();

        // 被购买的商品，用于后面更新商品销量和评分
        $products = collect([]);

        foreach ($orders as $order) {
            // ----- 随机设置总订单支付、发货、部分发货、退货退款或取消等状态
            // 10% 关闭订单
            if (random_int(0, 9) < 1) {
                $order->order_state = Order::ORDER_STATE_CANCELLED;
                $order->closed_at = $faker->dateTimeBetween('-29 days');
            } else {
                // 非关闭的订单，继续随机设置订单状态
                // 80% 的订单支付
                if (random_int(0, 9) < 8) {
                    $order->order_state = Order::ORDER_STATE_NEW;
                    $order->payment_state = Order::PAYMENT_STATE_PAID;
                    $order->paid_at = $faker->dateTimeBetween('-30 days');
                    $order->payment_method = $faker->randomElement(['wechat']);
                    $order->payment_no = $faker->uuid;

                    // 支付的订单 80% 设置发货
                    if (random_int(0, 9) < 8) {
                        $order->delivered_at = now();
                        // 50% 全部发货，50% 部分发货
                        $order->shipment_state = random_int(0, 9) < 5 ? Order::SHIPMENT_STATE_DELIVERED : Order::SHIPMENT_STATE_PARTIALLY_DELIVERED;

                        // 80% 已收货
                        if (random_int(0, 9) < 8) {
                            if ($order->shipment_state === Order::SHIPMENT_STATE_DELIVERED) {
                                // 全部发货时：全部收货
                                $order->receiving_state = Order::RECEIVING_STATE_RECEIVED;

                                // 30% 已退货
                                if (random_int(0, 9) < 3) {
                                    // 全部收货时：50% 全退货、50% 部分退货
                                    $order->shipment_refund_state = random_int(0, 9) < 5 ? Order::SHIPMENT_REFUND_STATE_REFUNDED : Order::SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED;
                                }
                            } else {
                                // 部分发货时：部分收货
                                $order->receiving_state = Order::RECEIVING_STATE_PARTIALLY_RECEIVED;

                                // 部分收货时、30% 部分退货
                                if (random_int(0, 9) < 3) {
                                    $order->shipment_refund_state = Order::SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED;
                                }
                            }
                        }
                    }
                }
            }

            // ----- 每笔订单随机 1 - 3 个商品
            $items = factory(OrderItem::class, random_int(1, 3))->create([
                'user_id'  => $order->user_id,
                'order_id' => $order->id,
            ]);

            $shipFee = 0;

            foreach ($items as $item) {
                // ----- 生成 item 的每个实体记录
                for ($i = 0; $i < $item->qty; $i++) {
                    // 生成 item 的每个实体
                    $unit = $item->units()->make([]);
                    $unit->adjustment_total = $unit->orderAdjustments()->sum('amount');
                    $unit->payment_price = $item->price + $unit->adjustment_total;
                    $unit->save();
                }

                $item->load([
                    'units',
                    'productSku:id,weight,volume',
                    'product.expressFee:id,fee_type',
                ]);

                // 计算运费
                // $fees = $item->product->expressFee->items()->whereJsonContains('provinces', $order->address['province'])->first();
                $fees = $item->product->expressFee->items()->whereJsonContains('provinces', '广东省')->first();
                // 计算运费单位 重量/体积
                $skuShipUnit = $item->productSku->{$item->product->expressFee->fee_type};

                if ($skuShipUnit <= $fees->fees['first']) {
                    // 首重费
                    $shipFee += $fees->fees['first_fee'];
                } else {
                    // ((商品运费的单位 - 运费模板首单位) * 续费) + 首费
                    $shipFee += (($skuShipUnit - $fees->fees['first']) * $fees->fees['renew_fee']) + $fees->fees['first_fee'];
                }

                // ----- 根据生成的订单各个状态，更新 item
                // 已支付的订单
                if ($order->payment_state == Order::PAYMENT_STATE_PAID) {
                    // 订单运输状态为：全部发货和部分发货的
                    if (in_array($order->shipment_state, [Order::SHIPMENT_STATE_DELIVERED, Order::SHIPMENT_STATE_PARTIALLY_DELIVERED])) {
                        // ----- 生成物流记录
                        $shipment = factory(OrderShipment::class)->make([
                            'express_company_id' => 1,
                        ]);

                        $shipment->shipment_state = OrderShipment::SHIPMENT_STATE_DELIVERED;
                        $shipment->delivered_at = now();

                        if ($order->shipment_state === Order::SHIPMENT_STATE_DELIVERED) {
                            // 更新 item 运输状态为：全部发货
                            $item->shipment_state = OrderItem::SHIPMENT_STATE_DELIVERED;

                            $units = $item->units;
                        } else {
                            // 更新 item 运输状态为：部分发货
                            $item->shipment_state = OrderItem::SHIPMENT_STATE_PARTIALLY_DELIVERED;

                            $units = $item->units->random($faker->numberBetween(1, $item->units->count()));
                        }

                        // 订单收货状态为：全部收货和部分收货的
                        if (in_array($order->receiving_state, [Order::RECEIVING_STATE_RECEIVED, Order::RECEIVING_STATE_PARTIALLY_RECEIVED])) {
                            $shipment->shipment_state = OrderShipment::SHIPMENT_STATE_RECEIVED;
                            $shipment->received_at = now();

                            if ($order->receiving_state === Order::RECEIVING_STATE_RECEIVED) {
                                // 更新 item 收货状态为：全部收货
                                $item->receiving_state = OrderItem::RECEIVING_STATE_RECEIVED;
                            } else {
                                // 更新 item 收货状态为：部分收货
                                $item->receiving_state = OrderItem::RECEIVING_STATE_PARTIALLY_RECEIVED;
                            }

                            // 订单退货状态为：全部退货和部分退货的
                            if (in_array($order->shipment_refund_state, [Order::SHIPMENT_REFUND_STATE_REFUNDED, Order::SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED])) {
                                $shipment->shipment_state = OrderShipment::SHIPMENT_STATE_REFUNDED;
                                $shipment->refunded_at = now();

                                if ($order->shipment_refund_state === Order::SHIPMENT_REFUND_STATE_REFUNDED) {
                                    // 更新 item 退货状态为：全部退货
                                    $item->shipment_refund_state = OrderItem::SHIPMENT_REFUND_STATE_REFUNDED;
                                } else {
                                    // 更新 item 退货状态为：部分退货
                                    $item->shipment_refund_state = OrderItem::SHIPMENT_REFUND_STATE_PARTIALLY_REFUNDED;
                                }

                                // ----- 随机生成退款
                                if (random_int(0, 9) < 8) {
                                    $refund = factory(OrderItemRefund::class)->make();

                                    // 退款方式为：仅退款
                                    if ($refund->refund_method === OrderItemRefund::REFUND_METHOD_ONLY_REFUND) {
                                        // 随机退款数量
                                        $refundedQty = $faker->numberBetween(1, $item->qty);
                                        // 子订单每个实体实际支付的价格总和
                                        $unitsPaymentTotal = $units
                                            ->take($refundedQty)
                                            ->sum('payment_price');

                                        $refund->apply_total = $unitsPaymentTotal;
                                        $refund->apply_qty   = $refundedQty;
                                    } else {
                                        // 子订单每个实体实际支付的价格总和
                                        $unitsPaymentTotal = $units->sum('payment_price');

                                        $refund->apply_qty   = $item->qty;
                                        $refund->apply_total = $unitsPaymentTotal;
                                    }

                                    $refund->user()->associate($order->user_id);
                                    $refund->order()->associate($order);
                                    $refund->orderItem()->associate($item);
                                    $refund->save();

                                    foreach ($units as $unit) {
                                        $unit->orderItemRefund()->associate($refund);
                                        $unit->save();
                                    }
                                }
                            } else {
                                // 没有退货，随机生成评论
                                if (random_int(0, 9) < 8) {
                                    $item->has_reviewed = true;

                                    factory(OrderItemReview::class)->create([
                                        'order_item_id'  => $item->id,
                                        'user_id'        => $item->user_id,
                                        'product_id'     => $item->product_id,
                                        'product_sku_id' => $item->product_sku_id,
                                    ]);
                                }
                            }
                        }

                        $shipment->order()->associate($order);
                        $shipment->save();

                        // item 每个实体单位关联物流表
                        foreach ($units as $unit) {
                            $unit->orderShipment()->associate($shipment);
                            $unit->save();
                        }
                    }
                }

                $item->load([
                    'orderAdjustments:id,amount,order_item_id',
                    'refunds:id,apply_total,order_item_id,refund_state,apply_qty',
                    'units.orderShipment:id,shipment_state'
                ]);

                $item->adjustment_total = $item->orderAdjustments->sum('amount');
                $item->payment_total = $item->price_total + $item->adjustment_total;
                $item->refunded_total = $item->refunds->where('refund_state', OrderItemRefund::REFUND_STATE_SUCCEED)->sum('apply_total');
                $item->refunded_qty = $item->refunds->where('refund_state', OrderItemRefund::REFUND_STATE_SUCCEED)->sum('apply_qty');
                $item->has_applied_refund = $item->refunds->contains('refund_state', OrderItemRefund::REFUND_STATE_PENDING);

                // 更新 item 的退款状态
                if ($item->refunded_qty > 0) {
                    $item->refund_state = $item->refunded_qty == $item->qty ? OrderItem::REFUND_STATE_ALL : OrderItem::REFUND_STATE_PARTIALLY;
                }

                $deliveredQty = 0;
                $receivedQty = 0;
                $shipmentRefundedQty = 0;
                foreach ($item->units as $unit) {
                    if ($unit->orderShipment) {
                        if ($unit->orderShipment->shipment_state == OrderShipment::SHIPMENT_STATE_DELIVERED) {
                            $deliveredQty += 1;
                        }
                        if ($unit->orderShipment->shipment_state == OrderShipment::SHIPMENT_STATE_RECEIVED) {
                            $receivedQty += 1;
                        }
                        if ($unit->orderShipment->shipment_state == OrderShipment::SHIPMENT_STATE_REFUNDED) {
                            $shipmentRefundedQty += 1;
                        }
                    }
                }
                $item->delivered_qty = $deliveredQty + $receivedQty + $shipmentRefundedQty;
                $item->received_qty = $receivedQty + $shipmentRefundedQty;
                $item->shipment_refunded_qty = $shipmentRefundedQty;

                $item->save();
            }

            // ----- 生成运费 adjustment，运费只关联到总订单表
            if ($shipFee > 0) {
                $orderAdjustment = OrderAdjustment::query()->make([
                    'type'        => OrderAdjustment::TYPE_SHIPPING,
                    'label'       => OrderAdjustment::$typeMap[OrderAdjustment::TYPE_SHIPPING],
                    'is_included' => true,
                    'amount'      => $shipFee,
                ]);
                $orderAdjustment->order()->associate($order);
                $orderAdjustment->save();

                $order->carriage_total = $shipFee;
            }

            $order->load([
                'adjustments:id,amount,order_id',
                'itemRefunds:id,order_id,refund_state,apply_total,apply_qty',
                'shipments:id,order_id,shipment_state',
            ]);

            // 订单总价
            $orderTotal = $items->sum(function (OrderItem $item) {
                return $item->price * $item->qty;
            });

            // 计算总共多少件商品
            $itemSkuQty = $items->sum('qty');

            $order->item_sku_qty = $itemSkuQty;
            $order->delivered_qty = $items->sum('delivered_qty');
            $order->received_qty = $items->sum('received_qty');
            $order->refunded_qty = $items->sum('refunded_qty');
            $order->refunded_total = $order->itemRefunds->where('refund_state', OrderItemRefund::REFUND_STATE_SUCCEED)->sum('apply_total');
            $order->shipment_refunded_qty = $items->sum('shipment_refunded_qty');
            $order->original_total = $orderTotal;
            $order->adjustment_total = $order->adjustments->sum('amount');
            $order->payment_total = $order->original_total + $order->adjustment_total;
            // 判断是否有正在申请退款的订单
            $order->has_applied_refund = $order->itemRefunds->contains('refund_state', OrderItemRefund::REFUND_STATE_PENDING);

            // 所有 order_items 评论后订单设置已完成
            $isCompleted = $items->contains('has_reviewed', false);
            if (!$isCompleted) {
                $order->order_state = Order::ORDER_STATE_COMPLETED;
                $order->completed_at = now();
            }

            // 更新 order 的退款状态
            if ($order->refunded_total > 0) {
                $order->payment_state = $order->refunded_total == $order->payment_total ? Order::PAYMENT_STATE_REFUNDED : Order::PAYMENT_STATE_PARTIALLY_REFUNDED;
            }

            // 更新订单
            $order->save();

            // 将这笔订单的商品合并到商品集合中
            $products = $products->merge($items->pluck('product'));
        }

        // 更新商品销量等数据
        $this->updateProducts($products);
    }

    protected function updateProducts($products)
    {
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
