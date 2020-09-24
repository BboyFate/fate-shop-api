<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Admin\Models\AdminUser;
use App\Admin\Models\AdminImage;
use Faker\Generator as Faker;

$factory->define(AdminImage::class, function (Faker $faker) {
    $images = [
        AdminImage::TYPE_PRODUCT => config('app.image_product'),
        AdminImage::TYPE_AVATAR  => config('app.image_admin_avatar'),
    ];

    // 随机取一个用户
    $admin = AdminUser::query()->inRandomOrder()->first();

    $type = $faker->randomElement(array_keys(AdminImage::$typeMap));

    return [
        'type'          => $type,
        'path'          => $images[$type],
        'admin_user_id' => $admin->id,
    ];
});
