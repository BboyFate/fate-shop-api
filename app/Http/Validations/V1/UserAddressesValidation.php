<?php

namespace App\Http\Validations\V1;

use App\Models\ProductSku;

class UserAddressesValidation
{
    public function storeOrUpdate()
    {
        return [
            'rules' => [
                'province'      => 'required|string',
                'city'          => 'required|string',
                'district'      => 'required|string',
                'address'       => 'required|string',
                'zip'           => 'required|integer',
                'contact_name'  => 'required|string',
                'contact_phone' => 'required|mobile_phone',
                'is_default'    => 'required|boolean',
            ]
        ];
    }
}
