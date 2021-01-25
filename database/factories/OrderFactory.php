<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Order;
use App\Models\User;

$factory->define(Order::class, function (Faker $faker) {
    // 随机取一个用户
    if (random_int(0, 9) > 2) {
        $user = User::query()->find(1);
    } else {
        $user = User::query()->inRandomOrder()->first();
    }
    // 随机取一个该用户的地址
    $address = $user->addresses()->inRandomOrder()->first();
    // 10% 关闭订单
    $isClosed = random_int(0, 9) < 1 ? true : false;

    $baseData = [
        'address'        => [
            'address'       => $address->full_address,
            'zip'           => $address->zip,
            'contact_name'  => $address->contact_name,
            'contact_phone' => $address->contact_phone,
        ],
        'remark'         => $faker->sentence,
        'is_closed'      => $isClosed,
        'extra'          => [],
        'user_id'        => $user->id,
        'original_total' => 0,
        'payment_total'  => 0,
        'qty_item'       => 0,
        'ip_address'     => ip2long($faker->ipv4),
        'order_state'    => Order::ORDER_STATE_PENDING,
        'payment_state'  => Order::PAYMENT_STATE_PENDING,
        'shipment_state' => Order::SHIPMENT_STATE_PENDING,
    ];

    if ($isClosed) {
        $baseData = array_merge($baseData, [
            'order_state' => Order::ORDER_STATE_CANCELLED,
        ]);

        return $baseData;
    }

    // 80% 订单支付
    if (random_int(0, 9) < 8) {
        $baseData = array_merge($baseData, [
            'paid_at'        => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
            'order_state'    => Order::ORDER_STATE_NEW,
            'payment_state'  => Order::PAYMENT_STATE_PAID,
            //'shipment_state' => random_int(0, 9) < 5 ? Order::SHIPMENT_STATE_PENDING : Order::SHIPMENT_STATE_READY,
        ]);
        // 订单支付里面的 80% 设置已发货（默认待发货）
        $isDelivered = random_int(0, 9) < 8 ? true : false;
        if ($isDelivered) {
            $baseData = array_merge($baseData, [
                'shipment_state' => Order::SHIPMENT_STATE_FULL_DELIVERED,
            ]);
            // 发货里面的 80% 已收货
            $isReceived = random_int(0, 9) < 8 ? true : false;
            if ($isReceived) {
                $baseData = array_merge($baseData, [
                    'shipment_state' => Order::SHIPMENT_STATE_FUll_RECEIVED,
                ]);
            }
        } else {
            // 剩余的在备货
            if (random_int(0, 9) < 8) {
                $baseData['shipment_state'] = Order::SHIPMENT_STATE_READY;
            } else {
                $baseData['shipment_state'] = Order::SHIPMENT_STATE_PENDING;
            }
        }

        return $baseData;
    }

    return $baseData;
});
