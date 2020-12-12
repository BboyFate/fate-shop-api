<?php

namespace App\Http\Controllers\V1;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;
use App\Models\Order;
use App\Models\UserAddress;
use App\Models\ProductSku;
use App\Models\CrowdfundingProduct;
use App\Models\Product;
use App\Http\Queries\OrderQuery;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use App\Events\OrderReviewed;
use Carbon\Carbon;

class OrdersController extends Controller
{
    /**
     * 订单列表
     *
     * @param Request $request
     * @param OrderQuery $query
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request, OrderQuery $query)
    {
        $orders = $query->where('user_id', $request->user()->id)->paginate();

        return OrderResource::collection($orders);
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
        $this->validateRequest($request, $this->storeRequestValidationRules($user, $request->input('items')));

        $address = UserAddress::find($request->input('address_id'));

        $order = $orderService->store($user, $address, $request->input('remark'), $request->input('items'));

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
     * 订单评价
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function sendReview(Request $request, $id)
    {
        $this->validateRequest($request, $this->sendReviewRequestValidationRules($request->route('order')));
        $order = Order::query()->findOrFail($id);
        $this->authorize('own', $order);

        if (! $order->paid_at) {
            return $this->response->errorForbidden('该订单未支付，不可评价');
        }

        if ($order->reviewed) {
            return $this->response->errorForbidden('该订单已评价，不可重复提交');
        }

        $reviews = $request->input('reviews');

        \DB::transaction(function () use ($reviews, $order) {
            foreach ($reviews as $review) {
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating'      => $review['rating'],
                    'review'      => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);
            }

            // 将订单标记为已评价
            $order->update(['reviewed' => true]);

            event(new OrderReviewed($order));
        });

        return $this->response->success();
    }

    /**
     * 订单申请退款
     *
     * @param Request $request
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function applyRefund(Request $request, $id)
    {
        $order = Order::query()->findOrFail($id);
        $this->authorize('own', $order);

        if (! $order->paid_at) {
            return $this->response->errorForbidden('该订单未支付，不可退款');
        }

        if ($order->type === Order::TYPE_CROWDFUNDING) {
            return $this->response->errorForbidden('众筹订单不支持退款');
        }

        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            return $this->response->errorForbidden('该订单已经申请过退款，请勿重复申请');
        }

        // 将用户输入的退款理由放到订单的 extra 字段中
        $extra                  = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');
        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra'         => $extra,
        ]);

        return $this->response->success();
    }

    public function storeRequestValidationRules($user, $items)
    {
        return [
            // 判断用户提交的地址 ID 是否存在于数据库并且属于当前用户
            // 后面这个条件非常重要，否则恶意用户可以用不同的地址 ID 不断提交订单来遍历出平台所有用户的收货地址
            'address_id'     => [
                'required',
                Rule::exists('user_addresses', 'id')->where('user_id', $user->id),
            ],
            'items'  => ['required', 'array'],
            'items.*.sku_id' => [ // 检查 items 数组下每一个子数组的 sku_id 参数
                'required',
                function ($attribute, $value, $fail) use ($items) {
                    if (! $sku = ProductSku::find($value)) {
                        return $fail('该商品不存在');
                    }

                    if (! $sku->product->on_sale) {
                        return $fail('该商品未上架');
                    }

                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }

                    // 获取当前索引
                    preg_match('/items\.(\d+)\.sku_id/', $attribute, $m);
                    $index = $m[1];
                    // 根据索引找到用户所提交的购买数量
                    $amount = $items[$index]['amount'];
                    if ($amount > 0 && $amount > $sku->stock) {
                        return $fail('该商品库存不足');
                    }
                },
            ],
            'items.*.amount' => ['required', 'integer', 'min:1'],
        ];
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

    public function sendReviewRequestValidationRules($orderId)
    {
        return [
            'reviews'          => ['required', 'array'],
            'reviews.*.id'     => [
                'required',
                Rule::exists('order_items', 'id')->where('order_id', $orderId)
            ],
            'reviews.*.rating' => ['required', 'integer', 'between:1,5'],
            'reviews.*.review' => ['required'],
        ];
    }
}
