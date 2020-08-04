<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use App\Jobs\CloseOrder;
use App\Exceptions\InternalException;
use App\Exceptions\InvalidRequestException;
use Carbon\Carbon;

class OrderService
{
    /**
     * 新增订单
     *
     * @param User $user
     * @param UserAddress $address
     * @param mixed $remark 订单备注
     * @param array $items 下单的商品的信息 { sku_id, amount }
     * @return mixed
     */
    public function store(User $user, UserAddress $address, $remark, $items)
    {
        $order = \DB::transaction(function () use ($user, $address, $remark, $items) {
            $address->update(['last_used_at' => Carbon::now()]);

            // 创建一个订单
            $order   = new Order([
                'address'      => [
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => $remark,
                'total_amount' => 0,
                'type'         => Order::TYPE_NORMAL,
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);

                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price'  => $sku->price,
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();

                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->destroy($skuIds);

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
        $order = \DB::transaction(function () use ($user, $address, $sku, $amount) {
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
        $order = \DB::transaction(function () use ($user, $addressData, $sku) {
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
     * 订单退款逻辑
     *
     * @param Order $order
     * @throws InternalException
     */
    public function refundOrder(Order $order)
    {
        // 判断该订单的支付方式
        switch ($order->payment_method) {
            case 'wechat':
                // 生成退款订单号
                $refundNo = Order::getAvailableRefundNo();

                app('wechat_pay')->refund([
                    'out_trade_no'  => $order->no,
                    'total_fee'     => $order->total_amount * 100,
                    'refund_fee'    => $order->total_amount * 100,
                    'out_refund_no' => $refundNo,
                    'notify_url'    => route('payment.wechat.refund_notify'),
                ]);

                $order->update([
                    'refund_no'     => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_PROCESSING,
                ]);

                break;
            default:
                throw new InternalException('未知订单支付方式：' . $order->payment_method);
                break;
        }
    }
}
