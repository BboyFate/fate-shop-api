<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use App\Models\ProductCategory;
use App\Admin\Models\AdminImage;
use Faker\Generator as Faker;

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

$factory->define(Product::class, function (Faker $faker) {
    // 从数据库中随机取一个类目
    $category = ProductCategory::query()->where('is_directory', false)->inRandomOrder()->first();

    $image = AdminImage::query()->where('type', AdminImage::TYPE_PRODUCT)->inRandomOrder()->first();

    return [
        'type'           => $faker->randomElement(array_keys(Product::$typeMap)),
        'title'          => $faker->word,
        'long_title'     => $faker->sentence,
        'description'    => $faker->sentence,
        'admin_image_id' => $image->id,
        'on_sale'        => true,
        'rating'         => $faker->numberBetween(0, 5),
        'sold_count'     => 0,
        'review_count'   => 0,
        'price'          => 0,
        'category_id'    => $category ? $category->id : null,
    ];
});
