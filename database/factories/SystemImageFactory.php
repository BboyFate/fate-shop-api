<?php

use App\Models\SystemImage;
use Faker\Generator as Faker;

$factory->define(SystemImage::class, function (Faker $faker) {
    return [
        'name' => $faker->name . '.jpg',
        'mime' => 'image/jpeg',
        'size' => $faker->numberBetween(100, 99999),
        'path' => config('app.url') . config('app.image_product'),
    ];
});
