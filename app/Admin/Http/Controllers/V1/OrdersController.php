<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Admin\Http\Resources\OrderResource;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Services\OrderService;

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

        if ($refundStatus = $request->input('refund_status')) {
            $builder->where('refund_status', $refundStatus);
        }

        if ($shipStatus = $request->input('ship_status')) {
            $builder->where('ship_status', $shipStatus);
        }

        $limit = $request->input('limit', 10);

        $orders = $builder->orderBy('created_at', 'desc')->paginate($limit);

        return $this->response->success(OrderResource::collection($orders));
    }

    public function show($id)
    {
        $order = Order::query()->findOrFail($id);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 订单发货
     *
     * @param Request $request
     * @param $id
     *
     * @return OrderResource|void
     */
    public function ship(Request $request, $id)
    {
        $order = Order::query()->findOrFail($id);
        $this->validateRequest($request, 'ship');

        if (! $order->paid_at) {
            return $this->response->errorBadRequest('未付款的订单不能发货');
        }

        if ($order->type === Order::TYPE_CROWDFUNDING &&
            $order->items[0]->product->crowdfunding->status !== CrowdfundingProduct::STATUS_SUCCESS) {
            return $this->response->errorBadRequest('众筹订单只能在众筹成功之后发货');
        }

        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            return $this->response->errorBadRequest('该订单已发货');
        }

        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            'ship_data'   => $request->only(['express_company', 'express_no'])
        ]);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 订单退款
     *
     * @param Request $request
     * @param $id
     * @param OrderService $orderService
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \App\Exceptions\InternalException
     */
    public function refund(Request $request, $id)
    {
        $order = Order::query()->findOrFail($id);

        $this->validateRequest($request, 'refund');

        // 判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            return $this->response->errorBadRequest('订单状态不正确');
        }
        // 是否同意退款
        if ($request->input('agree')) {
            // 清空拒绝退款理
            $extra = $order->extra ?: [];
            unset($extra['refund_disagree_reason']);
            $order->update([
                'extra' => $extra,
            ]);
            // 调用退款逻辑
            (new OrderService)->refundOrder($order);
        } else {
            // 将拒绝退款理由放到订单的 extra 字段中
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }

        return $this->response->success(new OrderResource($order));
    }
}
