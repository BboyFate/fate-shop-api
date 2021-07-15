<?php

namespace App\Admin\Http\Validations\V1\Products;

use App\Models\Products\Product;

class CommonProductsValidation
{
    public function requestCommonValidation()
    {
        $rules = [
            'title'                     => 'required',
            'long_title'                => 'required',
            'number'                    => 'size:64',
            'description'               => 'required',
            'on_sale'                   => 'required|boolean',
            'category_id'               => 'required|exists:product_categories,id',
            'express_fee_id'            => 'required|exists:express_fees,id',
            'image'                     => 'required|string',
            'banners'                   => 'array',
            'skus'                      => 'array',
            'skus.*.name'               => 'required',
            'skus.*.image'              => 'required|string',
            'skus.*.price'              => 'required|numeric|min:0.01',
            'skus.*.stock'              => 'required|integer|min:0',
            'skus.*.weight'             => 'required|numeric|min:0.01',
            'skus.*.volume'             => 'required|numeric|min:0.01',
            'skus.*.attributes'         => 'required|array',
            'skus.*.attributes.*.name'  => 'required',
            'skus.*.attributes.*.value' => 'required',
            'attributes'                => 'required|array',
            'attributes.*.name'         => 'required',
            'attributes.*.values'       => 'required|array',
            'properties'                => 'array',
            'properties.*.name'         => 'required',
            'properties.*.value'        => 'required',
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