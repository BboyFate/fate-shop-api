<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use App\Admin\Resources\OrderResource;
use App\Models\CrowdfundingProduct;
use App\Models\Order;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $builder = Order::query()
            ->with(['items.product', 'items.productSku']);

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('no', 'like', $like)
                    ->orWhereHas('items.product', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('long_title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    })
                    ->orWhereHas('items.productSku', function ($query) use ($like) {
                        $query->where('name', 'like', $like);
                    });
            });
        }

        $orders = $builder->orderBy('created_at', 'desc')->paginate();

        return OrderResource::collection($orders);
    }

    public function show($id)
    {
        $order = Order::query()->findOrFail($id);

        return new OrderResource($order);
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

        return new OrderResource($order);
    }
}
