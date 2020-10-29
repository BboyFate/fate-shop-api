<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\ProductSkuAttribute;
use App\Models\ProductSkuTemplate;

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

$factory->define(ProductSkuAttribute::class, function (Faker $faker) {
    $template = ProductSkuTemplate::query()->inRandomOrder()->first();

    $attributes = [];
    foreach ($template->value as $data) {
        $attributes[] = [
            'name'  => $data['name'],
            'value' => $data['attributes'],
        ];
    }

    return [
        'name'  => $faker->name,
        'value' => $attributes,
    ];
});
