<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Orders\OrderShipment;
use Faker\Generator as Faker;

$factory->define(OrderShipment::class, function (Faker $faker) {
    $baseData = [
        'express_no' => $faker->uuid,
        'extra'      => ['express_company' => $faker->name],
    ];

    return $baseData;
});
