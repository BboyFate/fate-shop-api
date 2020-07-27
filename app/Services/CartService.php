<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;

class CartService
{
    /**
     * 获取当前登录用户的购物车列表
     *
     * @return mixed
     */
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    /**
     * 商品添加到购物车
     *
     * @param int $skuId 商品 SKU 的 ID
     * @param int $amount 加购的数量
     * @return CartItem
     */
    public function store($skuId, $amount)
    {
        $user = Auth::user();

        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则直接叠加商品数量
            $item->update([
                'amount' => $item->amount + $amount,
            ]);
        } else {
            // 否则创建一个新的购物车记录
            $item = new CartItem(['amount' => $amount]);
            $item->user()->associate($user);
            $item->productSku()->associate($skuId);
            $item->save();
        }

        return $item;
    }

    /**
     * 从购物车移除某个商品
     *
     * @param int|array $skuIds 商品 SKU 的 ID
     */
    public function destroy($skuIds)
    {
        if (! is_array($skuIds)) {
            $skuIds = [$skuIds];
        }
        Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}
