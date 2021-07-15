<?php

namespace App\Http\Validations\V1;

use App\Models\Products\ProductSku;

class UserCartValidation
{
    public function store()
    {
        return [
            'rules' => [
                'sku_id'  => [
                    'required',
                    function ($attribute, $value, $fail) {
                        if (!$sku = ProductSku::query()->find($value)) {
                            return $fail('该商品不存在');
                        }

                        if (!$sku->product->on_sale) {
                            return $fail('该商品未上架');
                        }

                        if ($sku->stock === 0) {
                            return $fail('该商品已售完');
                        }

                        $amount = request()->input('amount');
                        if ($amount > 0 && $sku->stock < $amount) {
                            return $fail('该商品库存不足');
                        }
                    },
                ],
                'qty'  => ['required', 'integer', 'min:1'],
                '_accrue' => ['boolean']
            ],
        ];
    }
}
