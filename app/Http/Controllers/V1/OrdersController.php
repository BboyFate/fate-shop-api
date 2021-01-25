<?php

namespace App\Http\Controllers\V1;

use App\Http\Resources\UserAddressResource;
use App\Models\ExpressCompany;
use App\Models\ExpressFee;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\ProductSku;
use App\Models\OrderItemShipment;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Models\UserImage;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;
use App\Services\OrderService;
use App\Events\OrderItemReviewed;

class OrdersController extends Controller
{
    /**
     * 订单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $this->validateRequest($request);

        $user = $request->user();
        $limit = $request->input('limit', 10);
        $type = $request->input('type');

        $builder = OrderItem::query()
            ->select('order_items.*')
            ->where('order_items.user_id', $user->id)
            ->with([
                'shipment:id,shipment_state',
                'order:id,no,order_state,payment_state,address',
                'productSku:id,attributes,name,image'
            ]);

        switch ($type) {
            // 待付款
            case 'pending':
                $builder->join('orders', function ($join) {
                    $join->on('order_items.order_id', '=', 'orders.id')
                        ->where('orders.order_state', Order::ORDER_STATE_PENDING);
                });
                break;
            // 待发货
            case 'ship_pending':
                $builder->where('shipment_id', '=', 0)
                    ->where('is_applied_refund', false);

                $builder->join('orders', function ($join) {
                    $join->on('order_items.order_id', '=', 'orders.id')
                        ->where('orders.order_state', Order::ORDER_STATE_NEW);
                });
                break;
            // 待收货
            case 'delivered':
                $builder->where('is_applied_refund', false);

                $builder->join('orders', function ($join) {
                    $join->on('order_items.order_id', '=', 'orders.id')
                        ->where('orders.order_state', Order::ORDER_STATE_NEW);
                });
                $builder->join('order_item_shipments', function ($join) {
                    $join->on('order_items.shipment_id', '=', 'order_item_shipments.id')
                        ->where('order_item_shipments.shipment_state', OrderItemShipment::SHIPMENT_STATE_DELIVERED);
                });
                break;
            // 待评价
            case 'review':
                $builder->where('is_applied_refund', false)
                    ->where('is_reviewed', false);

                $builder->join('orders', function ($join) {
                    $join->on('order_items.order_id', '=', 'orders.id')
                        ->where('orders.order_state', Order::ORDER_STATE_NEW);
                });
                $builder->join('order_item_shipments', function ($join) {
                    $join->on('order_items.shipment_id', '=', 'order_item_shipments.id')
                        ->where('order_item_shipments.shipment_state', OrderItemShipment::SHIPMENT_STATE_RECEIVED);
                });
                break;
        }

        $orders = $builder->orderBy('created_at', 'desc')->paginate($limit);

        return $this->response->success(OrderItemResource::collection($orders));
    }

    /**
     * 购物车新增订单
     *
     * @param Request $request
     * @param OrderService $orderService
     *
     * @return OrderResource
     */
    public function store(Request $request, OrderService $orderService)
    {
        $this->validateRequest($request);

        $user = $request->user();

        $order = $orderService->store(
            $user,
            $request->input('address'),
            $request->input('remark'),
            $request->input('items')
        );

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 生成订单
     *
     * @param Request $request
     * @param OrderService $orderService
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function generateOrder(Request $request, OrderService $orderService)
    {
        $this->validateRequest($request);

        $result = $orderService->generateOrder($request->user(), $request->all());

        return $this->response->success($result);
    }

    /**
     * 计算订单金额
     *
     * @param Request $request
     * @param OrderService $orderService
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function calcOrder(Request $request, OrderService $orderService)
    {
        $this->validateRequest($request);

        $result = $orderService->calcOrderPrice(
            $request->user(),
            $request->input('items'),
            $request->input('address.province', null),
            $request->input('express_company_id', 0)
        );

        return $this->response->success([
            'original_total'    => $result['original_total'],
            'payment_total'     => $result['payment_total'],
            'express_fee_total' => $result['express_fee_total'],
        ]);
    }

    /**
     * 新增众筹订单
     *
     * @param Request $request
     * @param OrderService $orderService
     *
     * @return OrderResource
     */
    // TODO: 暂不可用
    public function crowdfunding(Request $request, OrderService $orderService)
    {
        $this->validateRequest($request, $this->crowdfundingRequestValidationRules());

        $user    = $request->user();
        $sku     = ProductSku::find($request->input('sku_id'));
        $address = UserAddress::find($request->input('address_id'));
        $amount  = $request->input('amount');

        $order = $orderService->crowdfunding($user, $address, $sku, $amount);

        return new OrderResource($order);
    }

    /**
     * 新增秒杀订单
     *
     * @param Request $request
     * @param OrderService $orderService
     *
     * @return OrderResource
     */
    // TODO: 暂不可用
    public function seckill(Request $request, OrderService $orderService)
    {
        $this->validateRequest($request, $this->seckillRequestValidationRules());

        $user    = $request->user();
        $sku     = ProductSku::find($request->input('sku_id'));
        $order = $orderService->seckill($user, $request->input('address'), $sku);

        return new OrderResource($order);
    }

    /**
     * 总订单详情
     *
     * @param $orderId
     *
     * @return OrderItemResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showOrder($orderId)
    {
        $order = Order::query()
            ->with(['items.productSku'])
            ->findOrFail($orderId);
        $this->authorize('own', $order);

        return $this->response->success(new OrderResource($order));
    }

    /**
     * 关闭总订单
     *
     * @param Request $request
     * @param $orderId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function closeOrder(Request $request, $orderId)
    {
        $order = Order::query()->findOrFail($orderId);
        $this->authorize('own', $order);

        if ($order->order_state != Order::ORDER_STATE_PENDING) {
            return $this->response->errorBadRequest('订单无法取消');
        }

        $order = (new OrderService)->closeOrder($order);

        return $this->response->success($order);
    }

    /**
     * 子订单 物流信息
     *
     * @param Request $request
     * @param $itemId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function orderItemShipmentShow(Request $request, $itemId)
    {
        $user = $request->user();

        $shipment = OrderItemShipment::query()
            ->select('order_item_shipments.*')
            ->with(['orderItem'])
            ->join('order_items', function ($join) use ($itemId, $user) {
                $join->on('order_item_shipments.id', '=', 'order_items.shipment_id')
                    ->where('order_items.id', $itemId)
                    ->where('order_items.user_id', $user->id);
            })
            ->findOrFail();

        return $this->response->success(new OrderItemShipment($shipment));
    }

    /**
     * 子订单 详情
     * 订单支付后才能查询
     *
     * @param $orderId
     * @param $itemId
     *
     * @return OrderItemResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showOrderItem($orderId, $itemId)
    {
        $orderItem = OrderItem::query()
            ->with(['order', 'refund', 'shipment', 'productSku'])
            ->findOrFail($itemId);
        $this->authorize('own', $orderItem->order);

        // 未支付的子订单不显示
        if (! $orderItem->order->payment_state == Order::PAYMENT_STATE_PENDING) {
            return $this->response->errorBadRequest();
        }

        return new OrderItemResource($orderItem);
    }

    /**
     * 子订单 确认收货
     *
     * @param $orderId
     * @param $itemId
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function receivedItem($orderId, $itemId)
    {
        $orderItem = OrderItem::query()->with(['order', 'shipment'])->findOrFail($itemId);
        $this->authorize('own', $orderItem->order);

        // 判断是否为已发货
        if ($orderItem->shipment->shipment_state !== OrderItemShipment::SHIPMENT_STATE_DELIVERED) {
            return $this->response->errorForbidden('发货状态不正确');
        }

        // 更新为已收货
        $orderItem->shipment()->update([
            'shipment_state' => OrderItemShipment::SHIPMENT_STATE_RECEIVED,
            'received_at'    => Carbon::now(),
        ]);

        return $this->response->success(new OrderItemResource($orderItem));
    }

    /**
     * 子订单 删除
     *
     * @param Request $request
     * @param $orderId
     * @param $itemId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroyItem(Request $request, $orderId, $itemId)
    {
        $order = Order::query()->findOrFail($orderId);
        $orderItem = OrderItem::query()->findOrFail($itemId);
        if ($order->id != $orderItem->order_id) {
            return $this->response->errorNotFound();
        }
        $this->authorize('own', $order);

        $orderItem->delete();
        return $this->response->noContent();
    }

    /**
     * 子订单 评论
     *
     * @param Request $request
     * @param $orderId
     * @param $itemId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function itemReviewStore(Request $request, $orderId, $itemId)
    {
        $this->validateRequest($request);

        $order = Order::query()->findOrFail($orderId);
        $orderItem = OrderItem::query()->findOrFail($itemId);

        if ($order->id != $orderItem->order_id) {
            return $this->response->errorBadRequest();
        }
        $this->authorize('own', $order);

        if (! $order->paid_at) {
            return $this->response->errorForbidden('该订单未支付，不可评价');
        }
        if ($orderItem->is_reviewed) {
            return $this->response->errorForbidden('该订单已评价，不可重复提交');
        }

        $reviewData = $request->only(['review', 'rating', 'image_ids']);
        $user = $request->user();

        $orderItem = DB::transaction(function () use ($user, $orderItem, $reviewData) {
            $images = UserImage::query()->unusedImages($reviewData['image_ids']);

            // 创建评价
            $review = $orderItem->review()->make([
                'rating'      => $reviewData['rating'],
                'review'      => $reviewData['review'],
                'reviewed_at' => Carbon::now(),
            ]);
            $review->user()->associate($user);
            $review->product()->associate($orderItem->product_id);
            $review->productSku()->associate($orderItem->product_sku_id);
            $review->save();

            $review->images()->saveMany($images);

            // 将订单标记为已评价
            $orderItem->update(['is_reviewed' => true]);

            event(new OrderItemReviewed($orderItem));

            return $orderItem;
        });

        return $this->response->success(new OrderItemResource($orderItem));
    }

    /**
     * 总订单 微信小程序支付
     * 所有子订单需要一起支付
     *
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function payByWechatMiniapp(Request $request, $orderId) {
        $order = Order::query()->findOrFail($orderId);
        $this->authorize('own', $order);

        // 校验订单状态
        if ($order->paid_at || $order->order_state === Order::ORDER_STATE_CANCELLED) {
            return $this->response->errorForbidden('订单状态不正确');
        }

        $result = app('wechat_pay')->miniapp([
            'out_trade_no' => $order->no,  // 微信商户订单流水号
            'total_fee'    => $order->payment_total * 100, // 微信支付的金额单位是分。
            'body'         => '支付的订单：' . $order->no, // 订单描述
            'openid'       => $request->user()->openid,
        ]);

        return $this->response->success($result);
    }

    public function crowdfundingRequestValidationRules()
    {
        return [
            'sku_id'     => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! $sku = ProductSku::find($value)) {
                        return $fail('该商品不存在');
                    }

                    // 众筹商品下单接口仅支持众筹商品的 SKU
                    if ($sku->product->type !== Product::TYPE_CROWDFUNDING) {
                        return $fail('该商品不支持众筹');
                    }

                    if (! $sku->product->on_sale) {
                        return $fail('该商品未上架');
                    }

                    if (! $sku->product->crowdfunding) {
                        return $fail('该商品不支持众筹');
                    }

                    // 还需要判断众筹本身的状态，如果不是众筹中则无法下单
                    if ($sku->product->crowdfunding->status !== CrowdfundingProduct::STATUS_FUNDING) {
                        return $fail('该商品众筹已结束');
                    }

                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }

                    if ($this->input('amount') > 0 && $sku->stock < $this->input('amount')) {
                        return $fail('该商品库存不足');
                    }
                },
            ],
            'amount'     => ['required', 'integer', 'min:1'],
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }

    public function seckillRequestValidationRules()
    {
        return [
            'address.province'      => 'required',
            'address.city'          => 'required',
            'address.district'      => 'required',
            'address.address'       => 'required',
            'address.zip'           => 'required',
            'address.contact_name'  => 'required',
            'address.contact_phone' => 'required',
            'sku_id'                => [
                'required',
                function ($attribute, $value, $fail) {
                    $stock = Redis::get('seckill_sku_' . $value);
                    if (is_null($stock)) {
                        return $fail('该商品不存在');
                    }
                    if ($stock < 1) {
                        return $fail('该商品已售完');
                    }

                    // 大多数用户在上面的逻辑里就被拒绝了
                    $sku = ProductSku::find($value);
                    if ($sku->product->seckill->is_before_start) {
                        return $fail('秒杀尚未开始');
                    }
                    if ($sku->product->seckill->is_after_end) {
                        return $fail('秒杀已经结束');
                    }

                    if (! $user = \Auth::user()) {
                        throw new AuthenticationException('请先登录');
                    }

                    $order = Order::query()
                        // 筛选出当前用户的订单
                        ->where('user_id', $this->user()->id)
                        ->whereHas('items', function ($query) use ($value) {
                            // 筛选出包含当前 SKU 的订单
                            $query->where('product_sku_id', $value);
                        })
                        ->where(function ($query) {
                            // 已支付的订单
                            $query->whereNotNull('paid_at')
                                // 或者未关闭的订单
                                ->orWhere('closed', false);
                        })
                        ->first();
                    if ($order) {
                        if ($order->paid_at) {
                            return $fail('您已经抢购了该商品');
                        }
                        return $fail('您已经下单了该商品，请到订单页面支付');
                    }
                }
            ],
        ];
    }
}
