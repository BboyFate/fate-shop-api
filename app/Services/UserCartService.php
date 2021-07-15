<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\Users\UserCartItem;

class UserCartService
{
    /**
     * 获取当前登录用户的购物车列表
     *
     * @return mixed
     */
    public function get($limit)
    {
        $items = Auth::user()
            ->cartItems()
            ->orderBy('created_at', 'desc')
            ->with(['productSku.product'])
            ->paginate($limit);

        return $items;
    }

    /**
     * 商品添加到购物车
     *
     * @param $skuId 商品 SKU 的 ID
     * @param $qty 加购的数量
     * @param bool $isAccrue 默认叠加加购数量
     * @return UserCartItem
     */
    public function store($skuId, $qty, $isAccrue = true)
    {
        $user = Auth::user();

        // 从数据库中查询该商品是否已经在购物车中
        if ($item = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            if ($isAccrue) {
                // 叠加商品数量
                $item->update([
                    'qty' => $item->qty + $qty,
                ]);
            } else {
                // 更新商品数量
                $item->update([
                    'qty' => $qty,
                ]);
            }
        } else {
            // 否则创建一个新的购物车记录
            $item = new UserCartItem(['qty' => $qty]);
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
