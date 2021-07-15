<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Expresses\ExpressCompany;
use Faker\Generator as Faker;

$factory->define(ExpressCompany::class, function (Faker $faker) {
    $data = [
        'sorted'     => 0,
        'is_default' => false,
        'is_showed'  => true,
    ];

    return $data;
});
