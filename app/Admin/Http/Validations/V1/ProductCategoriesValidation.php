<?php

namespace App\Admin\Http\Validations\V1;

use App\Models\ProductCategory;

class ProductCategoriesValidation
{
    public function store()
    {
        $isDirectory = request()->input('is_directory');

        return [
            'rules' => [
                'name'              => 'required|unique:product_categories,name',
                'is_directory'      => 'required|boolean',
                'parent_id'         => [
                    'integer',
                    function ($attribute, $value, $fail) use ($isDirectory) {
                        if (! $parent = ProductCategory::query()->find($value)) {
                            $fail('该父类目不存在');
                            return;
                        }

                        if ($isDirectory) {
                            $fail('创建顶层类目的，不能有父级类目');
                            return;
                        }
                    }],
                'attributes'        => 'array',
                'attributes.*.name' => 'filled',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules' => [
                'name'              => 'required|unique:product_categories,name',
                'attributes'        => 'array',
                'attributes.*.name' => 'filled',
            ]
        ];
    }
}
