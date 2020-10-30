<?php

namespace App\Admin\Validations\V1;

use App\Models\Product;
use App\Models\ProductSkuAttribute;

class CommonProductsValidation
{
    public function requestCommonValidation()
    {
        $rules = [
            'title'                  => 'required',
            'long_title'             => 'required',
            'description'            => 'required',
            'on_sale'                => 'required|boolean',
            'category_id'            => 'required|exists:product_categories,id',
            'image'                  => 'required|string',
            'banners'                => 'array',
            'skus'                   => 'array',
            'skus.*.name'            => 'required',
            'skus.*.image'           => 'required|string',
            'skus.*.price'           => 'required|numeric|min:0.01',
            'skus.*.stock'           => 'required|integer|min:0',
            'sku_attributes'         => 'required|array',
            'sku_attributes.*.name'  => 'required',
            'sku_attributes.*.value' => 'required|array',
            'properties'             => 'array',
            'properties.*.name'      => 'required',
            'properties.*.value'     => 'required',
        ];

        switch (request()->method()) {
            case 'PATCH':
                //$rules['sku_attributes.*.name'] = 'required|string|unique:product_sku_attributes,name';
        }

        if (request()->input('type') === Product::TYPE_CROWDFUNDING) {
            $rules['target_amount'] = 'required|numeric|min:0.01';
            $rules['end_at']        = 'required|date';
        }

        return [
            'rules' => $rules
        ];
    }

    public function skusDestroy()
    {
        $rules = [
            'sku_ids' => 'required|array',
        ];

        return [
            'rules' => $rules
        ];
    }
}
