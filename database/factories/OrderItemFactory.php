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
    $qty = random_int(1, 5); // 购买数量随机 1 - 5 份

    $baseData = [
        'qty'               => $qty,
        'price'             => $sku->price,
        'price_total'       => $sku->price * $qty,
        'product_id'        => $product->id,
        'product_sku_id'    => $sku->id,
        'is_reviewed'       => false,
        'is_applied_refund' => false,
        'extra'             => [],
        'shipment_id'       => 0,
    ];

    return $baseData;
});
