<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderItemRefund;
use App\Models\Systems\SysLog;
use App\Models\Orders\Order;
use App\Exceptions\InvalidRequestException;
use App\Events\Orders\OrderPaid;
use Carbon\Carbon;

class PaymentNotifiesController extends Controller
{
    /**
     * 订单 微信支付服务器端回调
     *
     * @return string
     */
    public function orderWechatPaid()
    {
        // 校验回调参数是否正确
        $data  = app('wechat_pay')->verify();

        $order = Order::query()->where('no', $data->out_trade_no)->first();

        if (! $order) {
            return 'fail';
        }
        if ($order->paid_at) {
            // 告知微信支付此订单已处理
            return app('wechat_pay')->success();
        }

        // 将订单标记为已支付
        $order->update([
            'paid_at'        => Carbon::now(),
            'payment_method' => Order::PAYMENT_METHOD_WECHAT,
            'payment_no'     => $data->transaction_id,
        ]);

        $this->orderAfterPaid($order);

        return app('wechat_pay')->success();
    }

    /**
     * 订单 微信退款通知
     *
     * @param Request $request
     * @return string
     * @throws InvalidRequestException
     */
    public function orderWechatRefunded(Request $request)
    {
        // 给微信的失败响应
        $failXml = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        $data = app('wechat_pay')->verify(null, true);

        // 查询对应的退款记录
        $refund = OrderItemRefund::query()
            ->with(['order', 'orderItem'])
            ->where('refund_no', $data['out_refund_no'])
            ->first();
        if(! $refund) {
            return $failXml;
        }

        if ($data['refund_status'] === 'SUCCESS') {
            DB::beginTransaction();
            try {
                // 退款状态改成退款成功
                $refund->refund_state = OrderItemRefund::REFUND_STATE_SUCCEED;

                // 子订单 退款数量叠加
                $refund->item->refunded_qty = $refund->item->refunded_qty + $refund->apply_qty;
                // 子订单 退款金额叠加
                $refund->item->refunded_total = $refund->item->refunded_total + $refund->apply_total;
                // 子订单 退款状态
                $refund->item->refund_state = $refund->item->refunded_qty === $refund->item->qty ? OrderItem::REFUND_STATE_ALL : OrderItem::REFUND_STATE_PARTIALLY;

                // 总订单 退款数量叠加
                $refund->order->refunded_qty = $refund->order->refunded_qty + $refund->apply_qty;
                // 总订单 退款金额叠加
                $refund->order->refunded_total = $refund->order->refunded_total + $refund->apply_total;
                // 总订单 支付状态
                $refund->order->payment_state = $refund->order->refunded_total == $refund->order->payment_total ? Order::PAYMENT_STATE_REFUNDED : Order::PAYMENT_STATE_PARTIALLY_REFUNDED;

                $refund->save();
                $refund->item->save();
                $refund->order->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                SysLog::query()->create([
                   'ip_address' => ip2long($request->ip()),
                    'extra' => $data,
                    'source_type' => SysLog::SOURCE_TYPE_ORDER_REFUND,
                ]);

                throw new InvalidRequestException($e->getMessage());
            }

        } else {
            // 退款失败，将具体状态存入 extra 字段，并表退款状态改成失败
            $extra = $refund->extra;
            $extra['refund_failed_code'] = $data['refund_status'];
            $refund->update([
                'refund_state' => OrderItemRefund::REFUND_STATE_FAILED,
            ]);
        }

        return app('wechat_pay')->success();
    }

    /**
     * 支付完成后触发事件
     *
     * @param Order $order
     */
    protected function orderAfterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
