<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        // 订单支付完成后的事件
        \App\Events\OrderPaid::class => [
            \App\Listeners\UpdateProductSoldCount::class,   // 更新商品销量
            \App\Listeners\UpdateCrowdfundingProductProgress::class,    // 更新众筹订单的进度
        ],
        // 订单评价完成后的事件
        \App\Events\OrderReviewed::class => [
            \App\Listeners\UpdateProductRating::class,  // 更新商品的评价
        ],
    ];
}
