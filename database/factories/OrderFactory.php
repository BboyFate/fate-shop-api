<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Order;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    // 随机取一个用户
    if (random_int(0, 9) > 2) {
        $user = User::query()->find(1);
    } else {
        $user = User::query()->inRandomOrder()->first();
    }
    // 随机取一个该用户的地址
    $address = $user->addresses()->inRandomOrder()->first();
    // 随机生成发货状态
    $shipStatus = $faker->randomElement(array_keys(Order::$shipStatusMap));
    // 10% 关闭订单
    $isClosed = random_int(0, 9) < 1 ? true : false;

    $baseData = [
        'address'        => [
            'address'       => $address->full_address,
            'zip'           => $address->zip,
            'contact_name'  => $address->contact_name,
            'contact_phone' => $address->contact_phone,
        ],
        'total_amount'   => 0,
        'remark'         => $faker->sentence,
        'closed'         => $isClosed,
        'extra'          => [],
        'user_id'        => $user->id,
    ];

    if ($isClosed) {
        return $baseData;
    }

    // 80% 订单支付
    $isPaid = random_int(0, 9) < 8 ? true : false;
    if ($isPaid) {
        $baseData = array_merge($baseData, [
            'paid_at'        => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
            'payment_method' => $faker->randomElement(['wechat', 'alipay']),
            'payment_no'     => $faker->uuid,
        ]);

        // 订单支付里面的 80% 设置已发货（默认待发货）
        $isDelivered = random_int(0, 9) < 8 ? true : false;
        if ($isDelivered) {
            $baseData = array_merge($baseData, [
                'ship_status'    => Order::SHIP_STATUS_DELIVERED,
                'ship_data'      => [
                    'express_company' => $faker->company,
                    'express_no'      => $faker->uuid,
                ],
                'shipped_at'     => $faker->dateTimeBetween('-20 days'),
            ]);

            // 发货里面的 80% 已收货
            $isReceived = random_int(0, 9) < 8 ? true : false;
            if ($isReceived) {
                $baseData = array_merge($baseData, [
                    'ship_status'  => Order::SHIP_STATUS_RECEIVED,
                    'delivered_at' => $faker->dateTimeBetween('-10 days'),
                ]);
            }
        }

        return $baseData;
    }


    return $baseData;

//    return [
//        'address'        => [
//            'address'       => $address->full_address,
//            'zip'           => $address->zip,
//            'contact_name'  => $address->contact_name,
//            'contact_phone' => $address->contact_phone,
//        ],
//        'total_amount'   => 0,
//        'remark'         => $faker->sentence,
//        'paid_at'        => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
//        'payment_method' => $faker->randomElement(['wechat', 'alipay']),
//        'payment_no'     => $faker->uuid,
//        'closed'         => false,
//        'ship_status'    => $shipStatus,
//        'ship_data'      => $shipStatus === Order::SHIP_STATUS_PENDING ? null : [
//            'express_company' => $faker->company,
//            'express_no'      => $faker->uuid,
//        ],
//        'shipped_at'     => $faker->dateTimeBetween('-20 days'),
//        'extra'          => [],
//        'user_id'        => $user->id,
//    ];
});
