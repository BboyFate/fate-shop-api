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
                        $qty = $items[$index]['qty'];
                        if ($qty > 0 && $qty > $sku->stock) {
                            return $fail('该商品库存不足');
                        }
                    },
                ],
                'items.*.qty'           => ['required', 'integer', 'min:1'],
            ]
        ];
    }

    /**
     * 订单评论
     */
    public function itemReviewStore()
    {
        return [
            'rules' => [
                'rating'    => 'required|integer|between:1,5',
                'review'    => 'required',
                'image_ids' => 'array',
            ]
        ];
    }

    /**
     * 子订单 申请退款
     */
    public function refundStore()
    {
        return [
            'rules' => [
                'reason'        => 'required',
                'refund_qty'    => 'required|integer',
                'refund_total'  => 'required|integer',
                'order_id'      => 'required|integer',
                'order_item_id' => 'required|integer',
            ]
        ];
    }

    /**
     * 生成订单
     */
    public function generateOrder()
    {
        $items = request()->input('items');

        return [
            'rules' => [
                'items'          => ['required', 'array'],
                'items.*.sku_id' => [ // 检查 items 数组下每一个子数组的 sku_id 参数
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
                        $qty = $items[$index]['qty'];
                        if ($qty > 0 && $qty > $sku->stock) {
                            return $fail('该商品库存不足');
                        }
                    },
                ],
                'items.*.qty'    => ['required', 'integer', 'min:1'],
            ]
        ];
    }

    /**
     * 计算订单价格
     */
    public function calcOrder()
    {
        $items = request()->input('items');

        return [
            'rules' => [
                'province'       => 'string',
                'items'          => ['required', 'array'],
                'items.*.sku_id' => [ // 检查 items 数组下每一个子数组的 sku_id 参数
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
                        $qty = $items[$index]['qty'];
                        if ($qty > 0 && $qty > $sku->stock) {
                            return $fail('该商品库存不足');
                        }
                    },
                ],
                'items.*.qty'    => ['required', 'integer', 'min:1'],
            ]
        ];
    }
}
