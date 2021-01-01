<?php

namespace App\Http\Controllers\V1;

use App\Events\OrderItemReviewed;
use App\Models\OrderItemReview;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserAddress;
use App\Models\ProductSku;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderItemResource;
use App\Services\OrderService;
use App\Events\OrderReviewed;

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

        $limit = $request->input('limit', 10);
        $user = $request->user();
        $builder = OrderItem::query()
            ->where('user_id', $user->id)
            ->with(['order', 'productSku']);

        $type = $request->input('type');

        if ($type != 'all') {
            $builder->whereHas('order', function ($query) {
                $query->where('closed', false);
            });

            switch ($type) {
                // 待付款
                case 'pending':
                    $builder->whereHas('order', function ($query) {
                        $query->where('paid_at', config('app.default_datetime'));
                    });
                    break;
                // 待发货
                case 'ship_pending':
                    $builder->whereHas('order', function ($query) {
                        $query->where('ship_status', Order::SHIP_STATUS_PENDING)
                            ->where('paid_at', '>', config('app.default_datetime'));
                    });
                    break;
                // 待收货
                case 'delivered':
                    $builder->whereHas('order', function ($query) {
                        $query->where('ship_status', Order::SHIP_STATUS_DELIVERED);
                    });
                    break;
                // 评价
                case 'review':
                    $builder->whereHas('order', function ($query) {
                        $query->where('ship_status', Order::SHIP_STATUS_RECEIVED);
                    });
                    break;
            }
        }

        if (in_array($type, ['ship_pending', 'delivered', 'review'])) {
            // 过滤掉一些申请售后
            $builder->where('refund_status', OrderItem::REFUND_STATUS_PENDING);
        }


        $orders = $builder->paginate($limit);

        return OrderItemResource::collection($orders);
    }

    /**
     * 退款售后列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function afterSales(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user = $request->user();

        $items =  OrderItem::query()
            ->where('user_id', $user->id)
            ->where('is_applied_refund', true)
            ->paginate($limit);

        return OrderItemResource::collection($items);
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
        $user = $request->user();
        $this->validateRequest($request);

        $order = $orderService->store(
            $user,
            $request->input('address'),
            $request->input('remark'),
            $request->input('items')
        );

        return new OrderResource($order);
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
     * 订单详情
     *
     * @param $id
     *
     * @return OrderResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $order = Order::query()->findOrFail($id);
        $this->authorize('own', $order);

        return new OrderResource($order);
    }

    /**
     * 用户确认收货
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function received($id)
    {
        $order = Order::query()->findOrFail($id);
        $this->authorize('own', $order);

        // 判断订单的发货状态是否为已发货
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            return $this->response->errorForbidden('发货状态不正确');
        }

        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);

        return $this->response->success();
    }

    /**
     * 子订单删除
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
     * 子订单评论
     *
     * @param Request $request
     * @param $orderId
     * @param $itemId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reviewItem(Request $request, $orderId, $itemId)
    {
        $this->validateRequest($request);
        $order = Order::query()->findOrFail($orderId);
        $orderItem = OrderItem::query()->findOrFail($itemId);
        if ($order->id != $orderItem->order_id) {
            return $this->response->errorNotFound();
        }
        $this->authorize('own', $order);

        if (! $order->paid_at || $order->paid_at == config('app.default_datetime')) {
            return $this->response->errorForbidden('该订单未支付，不可评价');
        }
        if ($orderItem->reviewed) {
            return $this->response->errorForbidden('该订单已评价，不可重复提交');
        }

        $reviewData = $request->input('review');
        $user = $request->user();

        \DB::transaction(function () use ($reviewData, $orderItem, $user) {
            // 创建评价
            $review = $orderItem->review()->create([
                'rating'      => $reviewData['rating'],
                'review'      => $reviewData['review'],
                'images'      => $reviewData['images'] || [],
                'reviewed_at' => Carbon::now(),
            ]);
            $review->user()->associate($user);
            $review->product()->associate($orderItem->product_id);
            $review->productSku()->associate($orderItem->product_sku_id);

            // 将订单标记为已评价
            $orderItem->update(['reviewed' => true]);

            event(new OrderItemReviewed($orderItem));
        });

        return $this->response->success();
    }

    /**
     * 子订单申请退款
     *
     * @param Request $request
     * @param $orderId
     * @param $itemId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function applyRefundItem(Request $request, $orderId, $itemId)
    {
        $order = Order::query()->findOrFail($orderId);
        $orderItem = OrderItem::query()->findOrFail($itemId);
        $this->authorize('own', $order);

        if (! $order->paid_at || $order->paid_at == config('app.default_datetime')) {
            return $this->response->errorForbidden('该订单未支付，不可退款');
        }
        if ($order->type === Order::TYPE_CROWDFUNDING) {
            return $this->response->errorForbidden('众筹订单不支持退款');
        }
        if ($orderItem->refund_status !== OrderItem::REFUND_STATUS_PENDING) {
            return $this->response->errorForbidden('该订单已经申请过退款，请勿重复申请');
        }

        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra                  = $orderItem->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $orderItem->update([
            'refund_status' => OrderItem::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $this->response->success();
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
