<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Expresses\ExpressFee;
use Faker\Generator as Faker;

$factory->define(ExpressFee::class, function (Faker $faker) {
    $data = [
        'is_default' => false,
        'fee_type'   => ExpressFee::FEE_TYPE_WEIGHT,
    ];

    return $data;
});
