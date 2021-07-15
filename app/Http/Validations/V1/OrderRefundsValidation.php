<?php

namespace App\Http\Validations\V1;

use App\Models\Orders\OrderItemRefund;

class OrderRefundsValidation
{
    /**
     * 子订单 申请退款
     */
    public function refundStore()
    {
        return [
            'rules' => [
                'refund_reason'        => 'required|string',
                'type'          => 'required|in:' . implode(array_keys(OrderItemRefund::$typeMap), ','),
                'refund_qty'    => 'required|integer',
                'refund_total'  => 'required|numeric',
                'cause_id'     => 'exists:order_refund_causes,id',
                'image_ids'     => [
                    'array',
//                    function ($attribute, $value, $fail) {
//                        $imageCount = Image::query()
//                            ->where('imageable_id', 0)
//                            ->whereIn('id', $value)
//                            ->count();
//                        if ($imageCount != count($value)) {
//                            return $fail('图片不正确');
//                        }
//                    },
                ],
            ]
        ];
    }
}
