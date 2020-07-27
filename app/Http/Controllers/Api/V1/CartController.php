<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\ProductSku;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 购物车列表
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        $cartItems = $this->cartService->get();

        return CartItemResource::collection($cartItems);
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
        $this->validateRequest($request, $this->storeRequestValidationRules($request->input('amount')));

        $this->cartService->store($request->input('sku_id'), $request->input('amount'));

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

    public function storeRequestValidationRules($amount)
    {
        return [
            'sku_id' => [
                'required',
                function ($attribute, $value, $fail) use ($amount) {
                    if (! $sku = ProductSku::find($value)) {
                        return $fail('该商品不存在');
                    }

                    if (! $sku->product->on_sale) {
                        return $fail('该商品未上架');
                    }

                    if ($sku->stock === 0) {
                        return $fail('该商品已售完');
                    }

                    if ($amount > 0 && $sku->stock < $amount) {
                        return $fail('该商品库存不足');
                    }
                },
            ],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
