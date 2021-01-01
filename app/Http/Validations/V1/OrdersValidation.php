<?php

namespace App\Http\Validations\V1;

use App\Models\ProductSku;
use Illuminate\Validation\Rule;

class OrdersValidation
{
    public function index()
    {
        return [
            'rules' => [
                'type' => 'required|string|in:all,pending,ship_pending,delivered,review'
            ],
        ];
    }

    public function store()
    {
        $items = request()->input('items');

        return [
            'rules' => [
                'address.province'      => 'required',
                'address.city'          => 'required',
                'address.district'      => 'required',
                'address.address'       => 'required',
                'address.zip'           => 'required',
                'address.contact_name'  => 'required',
                'address.contact_phone' => 'required',
                'items'                 => ['required', 'array'],
                'items.*.sku_id'        => [ // 检查 items 数组下每一个子数组的 sku_id 参数
                    'required',
                    function ($attribute, $value, $fail) use ($items) {
                        if (!$sku = ProductSku::query()->find($value)) {
                            return $fail('该商品不存在');
                        }

                        if (!$sku->product->on_sale) {
                            return $fail('该商品已下架');
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
                'items.*.amount'        => ['required', 'integer', 'min:1'],
            ]
        ];
    }

    /**
     * 订单评论
     */
    public function sendReview()
    {
        $orderId = request()->route('order');

        return [
            'rules' => [
                'reviews'           => ['required', 'array'],
                'reviews.*.item_id'     => [
                    'required',
                    Rule::exists('order_items', 'id')->where('order_id', $orderId)
                ],
                'reviews.*.rating'  => ['required', 'integer', 'between:1,5'],
                'reviews.*.review'  => ['required'],
                'reviews.*.images'  => ['array'],
            ]
        ];
    }
}
