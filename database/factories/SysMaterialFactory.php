<?php

use App\Models\Systems\SysMaterial;
use Faker\Generator as Faker;

$factory->define(SysMaterial::class, function (Faker $faker) {
    return [
        'name' => $faker->name . '.jpg',
        'mime' => 'image/jpeg',
        'type' => SysMaterial::TYPE_IMAGE,
        'size' => $faker->numberBetween(100, 99999),
        'path' => config('app.url') . config('app.image_product'),
    ];
});
