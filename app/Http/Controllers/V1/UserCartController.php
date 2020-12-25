<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\ProductSku;
use App\Http\Resources\UserCartItemResource;
use App\Services\UserCartService;

class UserCartController extends Controller
{
    protected $cartService;

    public function __construct(UserCartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 购物车列表
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $cartItems = $this->cartService->get($request->input('limit', 10));

        return UserCartItemResource::collection($cartItems);
    }

    /**
     * 添加商品到购物车
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validateRequest($request);

        $this->cartService->store(
            $request->input('sku_id'),
            $request->input('amount'),
            $request->input('_accrue', true)
        );

        return $this->response->created();
    }

    /**
     * 从购物车移除商品
     *
     * @param $skuId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($skuId)
    {
        $sku = ProductSku::query()->findOrFail($skuId);

        $this->cartService->destroy($sku->id);

        return $this->response->noContent();
    }
}
