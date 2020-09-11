<?php

namespace App\Admin\Validations\V1;

use App\Models\Product;
use App\Models\ProductSkuAttribute;

class CommonProductsValidation
{
    public function requestCommonValidation()
    {
        $categoryId = request()->input('category_id');

        $rules = [
            'title'                     => 'required',
            'long_title'                => 'required',
            'description'               => 'required',
            'on_sale'                   => 'required|boolean',
            'category_id'               => 'required|exists:product_categories,id',
            'product_image_id'          => 'required|exists:admin_images,id',
            'skus'                      => 'array',
            'skus.*.name'               => 'required',
            'skus.*.description'        => 'required',
            'skus.*.price'              => 'required|numeric|min:0.01',
            'skus.*.stock'              => 'required|integer|min:0',
            'skus.*.attributes'         => 'array',
            'skus.*.attributes.*.id'    => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($categoryId) {
                    $skuAttribute = ProductSkuAttribute::query()->find($value);
                    if (!$skuAttribute) {
                        $fail('不存在该商品 SKU 规格');
                        return;
                    }

                    if ($categoryId != $skuAttribute->product_category_id) {
                        $fail('规格和分类不对应');
                        return;
                    }
                }
            ],
            'skus.*.attributes.*.value' => 'required',
            'properties'                => 'array',
            'properties.*.name'         => 'required',
            'properties.*.value'        => 'required',
        ];

        switch (request()->method()) {
            case 'POST':
                $typeMapString = implode(',', array_keys(Product::$typeMap));
                $rules['type'] = 'required|string|in:' . $typeMapString;
        }

        if (request()->input('type') === Product::TYPE_CROWDFUNDING) {
            $rules['target_amount'] = 'required|numeric|min:0.01';
            $rules['end_at']        = 'required|date';
        }

        return [
            'rules' => $rules
        ];
    }
}
