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
}
