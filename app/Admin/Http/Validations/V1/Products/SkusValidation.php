<?php

namespace App\Admin\Http\Validations\V1\Products;

class SkusValidation
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
