<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\OrderItem;
use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(OrderItem::class, function (Faker $faker) {
    // 从数据库随机取一条商品
    $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();
    // 从该商品的 SKU 中随机取一条
    $sku = $product->skus()->inRandomOrder()->first();
    // 10% 的概率把订单标记为退款

    $baseData = [
        'amount'             => random_int(1, 5), // 购买数量随机 1 - 5 份
        'price'              => $sku->price,
        'product_id'         => $product->id,
        'product_sku_id'     => $sku->id,
        'extra'              => [],
        'reviewed'           => false,

        'refunded_money'     => 0,
        'refund_status'      => OrderItem::REFUND_STATUS_PENDING,
        'refund_no'          => null,
        'refunded_at'        => config('app.default_datetime'),
        'refund_verified_at' => config('app.default_datetime'),
    ];

    return $baseData;
});
