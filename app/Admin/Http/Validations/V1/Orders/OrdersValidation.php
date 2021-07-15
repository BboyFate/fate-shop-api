<?php

namespace App\Admin\Http\Validations\V1\Orders;

use App\Models\Expresses\ExpressCompany;

class OrdersValidation
{
    public function ship()
    {
        return [
            'rules' => [
                'express_company_id' => 'required|exists:' . (new ExpressCompany())->getTable() . ',id',
                'express_no'         => 'required',
            ]
        ];
    }

    public function partiallyShip()
    {
        return [
            'rules' => [
                'express_company_id'        => 'required|exists:' . (new ExpressCompany())->getTable() . ',id',
                'express_no'                => 'required',
                'delivers'                  => 'required|array',
                'delivers.*.item_id'        => 'required',
                'delivers.*.delivering_qty' => 'required|numeric',
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
