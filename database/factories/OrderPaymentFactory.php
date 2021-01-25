<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderPayment;
use Faker\Generator as Faker;

$factory->define(OrderPayment::class, function (Faker $faker) {

    $baseData = [
        'payment_method' => $faker->randomElement(OrderPayment::$paymentTypeMap),
        'payment_no'     => $faker->uuid,
        'paid_at'        => $faker->dateTimeBetween('-30 days'), // 30天前到现在任意时间点
    ];

    return $baseData;
});
