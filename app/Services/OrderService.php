<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Models\Orders\Order;
use App\Models\Products\ProductSku;
use App\Models\Users\User;
use App\Models\Users\UserAddress;
use App\Jobs\CloseOrder;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;

class OrderService
{
    /**
     * 计算订单
     *
     * @param User $user
     * @param array $items
     * @param $province
     * @param $expressCompanyId
     * @return array
     */
    public function calcOrderPrice(User $user, Array $items, $province, $expressCompanyId)
    {
        $originalTotal = 0;     // 订单总金额
        $noFeeWeightTotal = 0;   // 不包邮的商品总重量
        $noFeeVolumeTotal = 0;   // 不包邮的商品总体积
        $expressFeeTotal = 0;   // 邮费

        foreach ($items as $item) {
            $sku = ProductSku::query()
                ->select('product_id', 'price', 'weight', 'volume')
                ->with(['product:id,is_free_shipping'])
                ->find($item['sku_id']);

            // 商品金额
            $originalTotal += $sku->price * $item['qty'];

            // 不包邮的商品单位总和
            if ($sku->product->is_fee_shipping !== true) {
                $noFeeWeightTotal += $sku->weight;
                $noFeeVolumeTotal += $sku->volume;
            }
        }

        $userDefaultAddress = null;
        if (! $province) {
            $userDefaultAddress = $user->addresses()->default()->select(
                'is_default',
                'province',
                'city',
                'district',
                'address',
                'zip',
                'contact_name',
                'contact_phone'
            )->first();
            if ($userDefaultAddress) {
                $province = $userDefaultAddress->province;
            }
        }

        if ($province) {
            $ExpressService = new \App\Services\ExpressService();

            // 需计算运费的邮费单位和
            $feeUnits = [
                'weight' => $noFeeWeightTotal,
                'volume' => $noFeeVolumeTotal,
            ];
            $expressFeeItem = $ExpressService->getFeeItemByCompany($expressCompanyId, $province);
            $expressFeeTotal = $ExpressService->calcFee($expressFeeItem, $feeUnits);
        }

        return [
            'original_total'    => formatFloat($originalTotal),
            'express_fee_total' => formatFloat($expressFeeTotal),
            'payment_total'     => formatFloat($originalTotal + $expressFeeTotal),   // 实际续支付金额
        ];
    }

    /**
     * 新增订单
     *
     * @param User $user
     * @param array $addressData
     * @param $remark
     * @param $items
     * @return mixed
     */
    public function store(User $user, array $addressData, $remark, $items)
    {
        $order = DB::transaction(function () use ($user, $addressData, $remark, $items) {
            // $address->update(['last_used_at' => Carbon::now()]);

            $qtyItem = collect($items)->sum($items['qty']);

            // 创建一个订单
            $order   = new Order([
                'original_total'        => 0,   // 订单原价
                'payment_total'         => 0,   // 实际支付
                'adjustment_total'      => 0,   // 折扣的价格
                'item_adjustment_total' => 0,   // 子订单的折扣总计
                'address'               => [
                    'province'      => $addressData['province'],
                    'city'          => $addressData['city'],
                    'district'      => $addressData['district'],
                    'zip'           => $addressData['zip'],
                    'contact_name'  => $addressData['contact_name'],
                    'contact_phone' => $addressData['contact_phone'],
                ],
                'remark'                => $remark,
                'qty_item'              => $qtyItem,
                'type'                  => Order::TYPE_NORMAL,
            ]);
            $order->user()->associate($user);
            $order->save();

            $originalTotal = 0;
            foreach ($items as $data) {
                $sku = ProductSku::query()->find($data['sku_id']);

                $item = $order->items()->make([
                    'qty'         => $data['qty'],
                    'price'       => $sku->price,
                    'price_total' => $sku->price * $data['qty'],
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();

                $originalTotal += $sku->price * $data['qty'];
                if ($sku->decreaseStock($data['qty']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新订单总原价
            $order->update([
                'original_total' => $originalTotal
            ]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(UserCartService::class)->destroy($skuIds);

            return $order;
        });

        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }

    /**
     * 新增众筹订单
     *
     * @param User $user
     * @param UserAddress $address
     * @param ProductSku $sku
     * @param $amount
     * @return mixed
     */
    public function crowdfunding(User $user, UserAddress $address, ProductSku $sku, $amount)
    {
        $order = DB::transaction(function () use ($user, $address, $sku, $amount) {
            $address->update(['last_used_at' => Carbon::now()]);

            // 创建一个订单
            $order = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => '',
                'total_amount' => $sku->price * $amount,
                'type'         => Order::TYPE_CROWDFUNDING,
            ]);

            $order->user()->associate($user);
            $order->save();

            $item = $order->items()->make([
                'amount' => $amount,
                'price'  => $sku->price,
            ]);
            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();

            if ($sku->descreaseStock($amount) <= 0) {
                throw new InvalidRequestException('该商品库存不足');
            }

            return $order;
        });

        // 『众筹结束时间』减去『当前时间』得到『剩余秒数』
        $crowdfundingTtl = $sku->product->crowdfunding->end_at->getTimestamp() - time();
        // 『剩余秒数』与『默认订单关闭时间』取较小值作为『订单关闭时间』
        dispatch(new CloseOrder($order, min(config('app.order_ttl'), $crowdfundingTtl)));

        return $order;
    }

    public function seckill(User $user, array $addressData, ProductSku $sku)
    {
        $order = DB::transaction(function () use ($user, $addressData, $sku) {
            // 扣减对应 SKU 库存
            if ($sku->decreaseStock(1) <= 0) {
                throw new InvalidRequestException('该商品库存不足');
            }
            // 创建一个订单
            $order = new Order([
                'address'       => $addressData['province'] . $addressData['city'] . $addressData['district'] . $addressData['address'],
                'zip'           => $addressData['zip'],
                'contact_name'  => $addressData['contact_name'],
                'contact_phone' => $addressData['contact_phone'],
                'remark'        => '',
                'total_amount'  => $sku->price,
                'type'          => Order::TYPE_SECKILL,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();
            // 创建一个新的订单项并与 SKU 关联
            $item = $order->items()->make([
                'amount' => 1, // 秒杀商品只能一份
                'price'  => $sku->price,
            ]);
            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();

            // 完成下单逻辑时扣减 Redis 中的值
            Redis::decr('seckill_sku_'.$sku->id);

            return $order;
        });
        // 秒杀订单的自动关闭时间与普通订单不同
        dispatch(new CloseOrder($order, config('app.seckill_order_ttl')));

        return $order;
    }

    /**
     * 关闭订单
     *
     * @param Order $order
     *
     * @return Order|mixed|void
     */
    public function closeOrder(Order $order)
    {
        if ($order->order_state != Order::ORDER_STATE_PENDING) {
            return;
        }

        $order = DB::transaction(function () use ($order) {
            // 将订单的 closed 字段标记为 true，关闭订单
            $order->update([
                'is_closed'   => true,
                'order_state' => Order::ORDER_STATE_CANCELLED,
            ]);

            foreach ($order->items as $item) {
                // 循环遍历订单中的商品 SKU，将订单中的数量加回到 SKU 的库存中去
                $item->productSku->addStock($item->qty);

                // 当前订单类型是秒杀订单，并且对应商品是上架且尚未到截止时间
                if ($item->order->type === Order::TYPE_SECKILL
                    && $item->product->on_sale
                    && !$item->product->seckill->is_after_end) {
                    // 将 Redis 中的库存 +1
                    Redis::incr('seckill_sku_'.$item->productSku->id);
                }
            }
            return $order;
        });

        return $order;
    }
}
