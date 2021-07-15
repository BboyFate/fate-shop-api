<?php

namespace App\Http\Controllers\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Users\UserImage;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderItemRefund;
use App\Models\Orders\OrderRefundCause;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\OrderItemRefundResource;
use App\Http\Resources\OrderRefundCauseResource;
use Carbon\Carbon;

class OrderRefundsController extends Controller
{
    /**
     * 退款售后列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function refunds(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user = $request->user();
        $builder = OrderItemRefund::query()
            ->where('user_id', $user->id)
            ->with([
                'orderItem.productSku',
                'orderItem'
            ]);

        switch ($type = $request->input('type')) {
            // 待处理的售后申请
            case 'pending':
                $builder->where('refund_state', OrderItemRefund::REFUND_STATE_PENDING);
                break;
        }

        $refunds = $builder->paginate($limit);

        return OrderItemRefundResource::collection($refunds);
    }

    /**
     * 退款详情
     *
     * @param Request $request
     * @param $refundId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function refundShow(Request $request, $refundId)
    {
        $refund = OrderItemRefund::query()
            ->select('order_item_refunds.*')
            ->where('order_item_refunds.user_id', $request->user()->id)
            ->with(['orderItem.productSku'])
            ->join('order_items', 'order_items.id', '=', 'order_item_refunds.order_item_id')
            ->findOrFail($refundId);

        return $this->response->success(new OrderItemRefundResource($refund));
    }

    /**
     * 子订单 提交售后
     *
     * @param Request $request
     * @param $orderId
     * @param $itemId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function refundStore(Request $request, $orderId, $itemId)
    {
        $this->validateRequest($request);

        $order = Order::query()->findOrFail($orderId);
        $orderItem = OrderItem::query()->findOrFail($itemId);
        $this->authorize('own', $order);
        $user = $request->user();

        if (! $order->paid_at) {
            return $this->response->errorForbidden('该订单未支付，不可退款');
        }
        if ($order->type === Order::TYPE_CROWDFUNDING) {
            return $this->response->errorForbidden('众筹订单不支持退款');
        }
        if ($orderItem->is_applied_refund) {
            return $this->response->errorForbidden('该订单已经申请过退款，请勿重复申请');
        }
        $refundTotal = $request->input('refund_total');
        // 退款金额 不能大于 子订单金额
        if ($refundTotal > $orderItem->price_total) {
            return $this->response->errorForbidden('退款金额不允许');
        }

        $refundQty = $request->input('refund_qty');
        $imageIds = $request->input('image_ids');

        DB::beginTransaction();
        try {
            $extra                  = $orderItem->extra ?: [];
            $extra['refund_reason'] = $request->input('refund_reason');
            $extra['contact_phone'] = $request->input('contact_phone');
            $orderItem->update([
                'is_applied_refund' => true,
                'extra'             => $extra,
            ]);

            $images = UserImage::query()
                ->whereIn('id', $imageIds)
                ->where('imageable_id', 0)
                ->get();

            // 添加售后记录
            $refund = $orderItem->refund()->make([
                'type'           => $request->input('type'),
                'refund_no'      => OrderItemRefund::getAvailableRefundNo(),
                'refunded_qty'   => $refundQty,
                'refunded_total' => $refundTotal,
                'refunded_at'    => Carbon::now(),
            ]);
            $refund->user()->associate($user);
            $refund->order()->associate($order);
            $refund->save();

            $refund->images()->saveMany($images);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::commit();

        return $this->response->success(new OrderItemResource($orderItem));
    }

    /**
     * 申请售后原因选择列表
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function causes()
    {
        $reasons = OrderRefundCause::query()
            ->where('is_showed', true)
            ->orderBy('sorted')
            ->get();

        return $this->response->success(OrderRefundCauseResource::collection($reasons));
    }
}
