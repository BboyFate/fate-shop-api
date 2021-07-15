<?php

namespace App\Admin\Services;

use Illuminate\Support\Facades\DB;
use App\Exceptions\InvalidRequestException;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderItemRefund;
use App\Models\Orders\OrderShipment;
use App\Models\Expresses\ExpressCompany;

class OrderService
{
    /**
     * 递归 获取菜单
     * @param Order $order
     * @param ExpressCompany $company
     * @param $expressNo
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createShipment(Order $order, ExpressCompany $company, $expressNo)
    {
        $shipment = $order->shipments()->make([
            'shipment_state'     => OrderShipment::SHIPMENT_STATE_DELIVERED,
            'express_no'         => $expressNo,
            'extra'              => ['express_company' => $company->name],
            'delivered_at'       => now(),
        ]);
        $shipment->expressCompany()->associate($company);
        $shipment->save();

        return $shipment;
    }

    /**
     * 全部发货
     *
     * @param Order $order
     * @param array $data
     * @return Order
     * @throws InvalidRequestException
     */
    public function ship(Order $order, Array $data)
    {
        $expressCompany = ExpressCompany::query()->find($data['express_company_id']);

        DB::beginTransaction();
        try {
            $shipment = $this->createShipment($order, $expressCompany, $data['express_no']);

            foreach ($order->items as $item) {
                $item->shipment_state = OrderItem::SHIPMENT_STATE_DELIVERED;
                $item->delivered_qty = $item->qty;
                $item->save();

                // item 每个实体单位关联物流表
                foreach ($item->units as $unit) {
                    $unit->orderShipment()->associate($shipment);
                    $unit->save();
                }
            }

            $order->shipment_state = Order::SHIPMENT_STATE_DELIVERED;
            $order->delivered_at = now();
            $order->delivered_qty = $order->delivered_qty + $order->items->sum('delivered_qty');
            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new InvalidRequestException($e->getMessage());
        }

        return $order;
    }

    /**
     * 部分发货
     *
     * @param Order $order
     * @param array $data
     * @return Order
     * @throws InvalidRequestException
     */
    public function partiallyShip(Order $order, Array $data)
    {
        $expressCompany = ExpressCompany::query()->find($data['express_company_id']);

        DB::beginTransaction();
        try {
            $shipment = $this->createShipment($order, $expressCompany, $data['express_no']);

            foreach ($data['delivers'] as $deliver) {
                $item = $order->items->where('id', $deliver['item_id'])->first();

                if ($item->delivered_qty > $item->qty ) {
                    throw new InvalidRequestException("商品 {$item->productSku->name} 无法发货");
                }

                $item->delivered_qty = $item->delivered_qty + $deliver['delivering_qty'];
                $item->shipment_state = $item->delivered_qty === $item->qty ? OrderItem::SHIPMENT_STATE_DELIVERED : OrderItem::SHIPMENT_STATE_PARTIALLY_DELIVERED;
                $item->save();

                // item 每个实体单位关联物流表
                foreach ($item->units as $unit) {
                    $unit->orderShipment()->associate($shipment);
                    $unit->save();
                }
            }

            $order->delivered_at = now();
            $order->delivered_qty = $order->items->sum('delivered_qty');
            $order->shipment_state = $order->delivered_qty === $order->item_sku_qty ? Order::SHIPMENT_STATE_DELIVERED : Order::SHIPMENT_STATE_PARTIALLY_DELIVERED;
            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new InvalidRequestException($e->getMessage());
        }

        return $order;
    }

    /**
     * 订单退款逻辑
     *
     * @param OrderItemRefund $refund
     * @throws InvalidRequestException
     */
    public function refundOrder(OrderItemRefund $refund)
    {
        $refund->load(['order']);

        // 判断该订单的支付方式
        switch ($refund->order->payment_method) {
            case 'wechat':
                // 生成退款订单号
                $refundNo = OrderItemRefund::getAvailableRefundNo();

                app('wechat_pay')->refund([
                    'out_trade_no'  => $refund->order->no,
                    'refund_fee'    => $refund->apply_total * 100,
                    'out_refund_no' => $refundNo,
                    'total_fee'     => $refund->order->payment_total * 100,
                    'notify_url'    => route('api.v1.payment.wechat.refund_notify'),
                ]);

                $refund->update([
                    'refund_no'     => $refundNo,
                    'refund_state' => OrderItemRefund::REFUND_STATE_PROCESSING,
                ]);

                break;
            default:
                throw new InvalidRequestException('未知订单支付方式：' . $refund->order->payment_method);
                break;
        }
    }
}
