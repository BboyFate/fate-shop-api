<?php

namespace App\Admin\Http\Controllers\V1\Orders;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Orders\OrderItemRefund;
use App\Admin\Services\OrderService;
use App\Admin\Http\Resources\Orders\OrderItemRefundResource;

class OrderItemRefundsController extends Controller
{
    public function index(Request $request)
    {
        $builder = OrderItemRefund::query()
            ->with([
                'order.items',
                'user:id,nickname',
            ]);

        if ($search = $request->input('search', '')) {
            $like = $search . '%';
            $builder->where('refund_no', 'like', $like)
                ->orWhere('refund_no', 'like', $like);
        }

        // 状态查询
        if ($state = $request->input('state')) {
            switch ($state) {
                // 待审核
                case 'refund_pending':
                    $builder->where('refund_state', OrderItemRefund::REFUND_STATE_PENDING);
                    break;
                // 退款成功
                case 'refund_succeed':
                    $builder->where('refund_state', OrderItemRefund::REFUND_STATE_SUCCEED);
                    break;
                // 拒绝退款
                case 'refund_disagreed':
                    $builder->where('refund_state', OrderItemRefund::REFUND_STATE_DISAGREED);
                    break;
                // 取消申请
                case 'refund_cancelled':
                    $builder->where('refund_state', OrderItemRefund::REFUND_STATE_CANCELLED);
                    break;
            }
        }

        $limit = $request->input('limit', 10);
        $list = $builder->orderBy('applied_at', 'desc')->paginate($limit);

        return $this->response->success(OrderItemRefundResource::collection($list));
    }

    public function show($refundId)
    {
        $data = OrderItemRefund::query()
            ->with([
                'order.items',
                'user:id,nickname',
            ])
            ->findOrFail($refundId);

        return $this->response->success(new OrderItemRefundResource($data));
    }

    /**
     * 退款
     * @param Request $request
     * @param $refundId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \App\Exceptions\InvalidRequestException
     */
    public function handleRefund(Request $request, $refundId)
    {
        $refund = OrderItemRefund::query()->with(['orderItem'])->findOrFail($refundId);
        $this->validateRequest($request, 'refund');

        // 判断订单状态是否正确
        if ($refund->refund_state !== OrderItemRefund::REFUND_STATE_PENDING) {
            return $this->response->errorBadRequest('订单状态不正确');
        }
        if ($refund->apply_total > $refund->orderItem->payment_total) {
            return $this->response->errorBadRequest('不能超过支付金额');
        }

        // 是否同意退款
        if ($request->input('is_agree')) {
            // 清空拒绝退款理
            $extra = $refund->extra ?: [];
            unset($extra['disagree_reason']);
            $refund->update([
                'extra' => $extra,
            ]);
            // 调用退款逻辑
            (new OrderService)->refundOrder($refund);
        } else {
            // 将拒绝退款理由放到订单的 extra 字段中
            $extra = $refund->extra ?: [];
            $extra['disagree_reason'] = $request->input('reason');
            // 将订单的退款状态改为未退款
            $refund->update([
                'refund_state' => OrderItemRefund::REFUND_STATE_PENDING,
                'extra'        => $extra,
            ]);
        }

        return $this->response->success(new OrderItemRefundResource($refund));
    }
}
