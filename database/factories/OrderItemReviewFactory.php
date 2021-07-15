<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Orders\OrderItemReview;

$factory->define(OrderItemReview::class, function (Faker $faker) {
    $isVerified = random_int(0, 9) > 5 ? true : false;

    return [
        'is_verified' => $isVerified,
        'verified_at' => $isVerified ? now() : null,
        'review'      => $faker->sentence,
        'rating'      => random_int(1, 5),  // 随机评分 1 - 5
    ];
});
