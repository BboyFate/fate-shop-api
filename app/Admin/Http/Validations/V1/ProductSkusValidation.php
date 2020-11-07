<?php

namespace App\Admin\Http\Validations\V1;

class ProductSkusValidation
{
    public function skusDestroy()
    {
        return [
            'rules' => [
                'sku_ids' => 'required|array',
            ]
        ];
    }
}
