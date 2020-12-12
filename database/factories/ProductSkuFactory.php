<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\ProductSku;
use App\Models\SystemImage;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(ProductSku::class, function (Faker $faker) {
    $image = SystemImage::query()->inRandomOrder()->first();

    return [
        'image' => $image->path,
        'price' => $faker->randomNumber(4),
        'stock' => $faker->randomNumber(5),
    ];
});
