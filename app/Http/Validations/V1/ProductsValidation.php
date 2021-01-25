<?php

namespace App\Http\Validations\V1;

class ProductsValidation
{
    public function favoriteDestroys()
    {
        $rules = [
            'product_ids' => 'required|array',
        ];

        return [
            'rules' => $rules
        ];
    }
}
