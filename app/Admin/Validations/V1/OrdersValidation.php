<?php

namespace App\Admin\Validations\V1;

class OrdersValidation
{
    public function ship()
    {
        return [
            'rules' => [
                'express_company' => 'required',
                'express_no'      => 'required',
            ]
        ];
    }

    public function refund()
    {
        return [
            'rules' => [
                'agree'  => 'required|boolean',
                'reason' => 'required_if:agree,false', // 拒绝退款时需要输入拒绝理由
            ]
        ];
    }
}
