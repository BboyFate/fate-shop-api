<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use App\Models\Order;

class CloseOrder implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * CloseOrder constructor.
     * @param Order $order
     * @param $delay
     */
    public function __construct(Order $order, $delay)
    {
        $this->order = $order;

        // 设置延迟的时间，delay() 方法的参数代表多少秒之后执行
        $this->delay($delay);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->order->paid_at) {
            return;
        }

        \DB::transaction(function () {
            // 将订单的 closed 字段标记为 true，关闭订单
            $this->order->update(['closed' => true]);

            foreach ($this->order->items as $item) {
                // 循环遍历订单中的商品 SKU，将订单中的数量加回到 SKU 的库存中去
                $item->productSku->addStock($item->amount);

                // 当前订单类型是秒杀订单，并且对应商品是上架且尚未到截止时间
                if ($item->order->type === Order::TYPE_SECKILL
                    && $item->product->on_sale
                    && !$item->product->seckill->is_after_end) {
                    // 将 Redis 中的库存 +1
                    Redis::incr('seckill_sku_'.$item->productSku->id);
                }
            }
        });
    }
}
