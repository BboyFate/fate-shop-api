<?php

namespace App\Admin\Http\Controllers\V1\Orders;

use Illuminate\Http\Request;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItemRefund;
use App\Admin\Services\OrderService;
use App\Admin\Http\Controllers\V1\Controller;
use App\Admin\Http\Resources\Orders\OrderResource;
use App\Admin\Http\Resources\Orders\OrderItemRefundResource;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $builder = Order::query()
            ->with(['items.product.crowdfunding', 'items.productSku', 'user:id,nickname']);

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('no', 'like', $like)
                    ->orWhereHas('items.product', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('long_title', 'like', $like);
                    })
                    ->orWhereHas('items.productSku', function ($query) use ($like) {
                        $query->where('name', 'like', $like);
                    });
            });
        }

        // 订单主状态查询
        if ($orderState = $request->input('order_state')) {
            $builder->where('order_state', $orderState);
        }
        // 支付状态查询
        if ($paymentState = $request->input('payment_state')) {
            $builder->where('payment_state', $paymentState);
        }
        // 运输状态查询
        if ($shipmentState = $request->input('shipment_state')) {
            $builder->where('shipment_state', $shipmentState);
        }
        // 订单状态查询
        if ($state = $request->input('state')) {
            switch ($state) {
                // 待付款
                case 'payment_pending':
                    $builder->where('order_state', Order::ORDER_STATE_PENDING);
                    break;
                // 待发货
                case 'shipment_pending':
                    $builder->where('order_state', Order::ORDER_STATE_NEW)
                        ->where('shipment_state', Order::SHIPMENT_STATE_PENDING);
                    break;
                // 全部发货
                case 'delivered':
                    $builder->where('order_state', Order::ORDER_STATE_NEW)
                        ->where('shipment_state', Order::SHIPMENT_STATE_DELIVERED);
                    break;
                // 部分发货
                case 'partially_delivered':
                    $builder->where('order_state', Order::ORDER_STATE_NEW)
                        ->where('shipment_state', Order::SHIPMENT_STATE_PARTIALLY_DELIVERED);
                    break;
                // 已完成
                case 'completed':
                    $builder->where('order_state', Order::ORDER_STATE_COMPLETED);
                    break;
                // 已取消
                case 'cancelled':
                    $builder->where('order_state', Order::ORDER_STATE_CANCELLED);
                    break;
                // 全部退款
                case 'refunded':
                    $builder->where('payment_state', Order::PAYMENT_STATE_REFUNDED);
                    break;
                // 部分退款
                case 'partially_refunded':
                    $builder->where('payment_state', Order::PAYMENT_STATE_PARTIALLY_REFUNDED);
                    break;
                // 退款待处理
                case 'has_applied_refund':
                    $builder->where('has_applied_refund', true);
                    break;
            }
        }

        $limit = $request->input('limit', 10);

        $orders = $builder->orderBy('id', 'desc')->paginate($limit);

        return $this->response->success(OrderResource::collection($orders));
    }

    public function show($orderId)
    {
        $order = Order::query()
            ->with([
                'items.orderShipments',
                'items.orderAdjustments',
                'items.product',
                'items.productSku',
                'items.product.expressFee',
                'items.refunds',
            ])
            ->findOrFail($orderId);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 订单全部发货
     *
     * @param Request $request
     * @param OrderService $service
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function ship(Request $request, OrderService $service, $orderId)
    {
        $this->validateRequest($request, 'ship');

        $order = Order::query()
            ->select(
                'id',
                'paid_at',
                'shipment_state'
            )
            ->with(['items.units'])
            ->findOrFail($orderId);

        if (! $order->paid_at) {
            return $this->response->errorBadRequest('未付款的订单不能发货');
        }
        if ($order->shipment_state !== Order::SHIPMENT_STATE_PENDING) {
            return $this->response->errorBadRequest('该订单已发货');
        }

        $order = $service->ship($order, [
            'express_company_id' => $request->input('express_company_id'),
            'express_no'         => $request->input('express_no'),
        ]);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 部分发货
     *
     * @param Request $request
     * @param OrderService $service
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function partiallyShip(Request $request, OrderService $service, $orderId)
    {
        $this->validateRequest($request, 'partiallyShip');

        $delivers = $request->input('delivers');
        $itemIds = [];
        foreach ($delivers as $deliver) {
            $itemIds[] = $deliver['item_id'];
        }

        $order = Order::query()
            ->select(
                'id',
                'paid_at',
                'shipment_state'
            )
            ->with([
                'items:id,order_id,delivered_qty,qty',
                'items.productSku:id,name',
                'items.units' => function ($query) use ($itemIds) {
                    $query->whereIn('order_item_id', $itemIds);
                },
            ])
            ->findOrFail($orderId);

        if (! $order->paid_at) {
            return $this->response->errorBadRequest('未付款的订单不能发货');
        }
        if (!in_array($order->shipment_state, [Order::SHIPMENT_STATE_PENDING, Order::SHIPMENT_STATE_PARTIALLY_DELIVERED])) {
            return $this->response->errorBadRequest('该订单已经无法发货');
        }

        $order = $service->partiallyShip($order, [
            'express_company_id' => $request->input('express_company_id'),
            'express_no'         => $request->input('express_no'),
            'delivers'           => $delivers,
        ]);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 订单的退款列表
     * @param $orderId
     * @param $orderItemId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function getOrderItemRefunds($orderId, $orderItemId, Request $request)
    {
        $builder = OrderItemRefund::query()
            ->where('order_id', $orderId)
            ->where('order_item_id', $orderItemId)
            ->with(['order.items']);

        $limit = $request->input('limit', 10);

        $list = $builder->orderBy('applied_at', 'desc')->paginate($limit);

        return $this->response->success(OrderItemRefundResource::collection($list));
    }
}
