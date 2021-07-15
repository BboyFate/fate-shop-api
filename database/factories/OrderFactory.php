<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Orders\Order;
use App\Models\Users\User;

$factory->define(Order::class, function (Faker $faker) {
    // 随机取一个用户
    if (random_int(0, 9) > 2) {
        $user = User::query()->find(1);
    } else {
        $user = User::query()->inRandomOrder()->first();
    }
    // 随机取一个该用户的地址
    $address = $user->addresses()->inRandomOrder()->first();

    return [
        'address'        => [
            'contact_name'  => $address->contact_name,
            'contact_phone' => $address->contact_phone,
            'province'      => $address->province,
            'city'          => $address->city,
            'district'      => $address->district,
            'address'       => $address->address,
            'zip'           => $address->zip,
        ],
        'remark'         => $faker->sentence,
        'extra'          => [],
        'user_id'        => $user->id,
        'original_total' => 0,
        'payment_total'  => 0,
        'item_sku_qty'   => 0,
        'ip_address'     => ip2long($faker->ipv4),
    ];
});
