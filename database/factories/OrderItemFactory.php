<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Orders\OrderItem;
use App\Models\Products\Product;
use Faker\Generator as Faker;

$factory->define(OrderItem::class, function (Faker $faker) {
    // 从数据库随机取一条商品
    $product = Product::query()->where('on_sale', true)->inRandomOrder()->first();
    // 从该商品的 SKU 中随机取一条
    $sku = $product->skus()->inRandomOrder()->first();
    // 购买数量随机 2 - 5 份，必须设置最少 2 以上，会影响后面生成的部分发货
    $qty = random_int(2, 5);

    $baseData = [
        'qty'               => $qty,
        'price'             => $sku->price,
        'price_total'       => $sku->price * $qty,
        'product_id'        => $product->id,
        'product_sku_id'    => $sku->id,
        'extra'             => [],
        'payment_total'     => 0,
    ];

    return $baseData;
});
