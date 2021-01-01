<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderItemReview;

$factory->define(OrderItemReview::class, function () {
    return [
        'is_verified' => false,
    ];
});
