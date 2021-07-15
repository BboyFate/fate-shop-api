<?php

namespace App\Admin\Http\Validations\V1\Orders;

class OrderItemRefundsValidation
{
    public function refund()
    {
        return [
            'rules' => [
                'is_agree'        => 'required|boolean',
                'disagree_reason' => 'required_if:is_agree,false', // 拒绝退款时需要输入拒绝理由
                'apply_total'     => 'required|numeric',
            ]
        ];
    }
}
