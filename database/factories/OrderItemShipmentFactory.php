<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderItemShipment;
use Faker\Generator as Faker;

$factory->define(OrderItemShipment::class, function (Faker $faker) {
    $baseData = [
        'express_no'   => $faker->uuid,
        'express_data' => [],
    ];

    return $baseData;
});
