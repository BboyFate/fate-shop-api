<?php

namespace App\Admin\Http\Validations\V1;

use Illuminate\Support\Arr;
use App\Models\ProductCategory;

class ProductCategoriesValidation
{
    protected function commonRules()
    {
        return [
            'sorted'    => 'required|integer',
            'is_showed' => 'required|boolean',
            'parent_id' => 'required|integer',
            'image'     => 'string',
        ];
    }

    public function store()
    {
        return [
            'rules' => Arr::collapse([$this->commonRules(), [
                'name' => 'required|unique:product_categories,name',
            ]])
        ];
    }

    public function update()
    {
        return [
            'rules' => Arr::collapse([$this->commonRules(), [
                'name' => 'required|unique:product_categories,name,' . request()->route('id'),
            ]])
        ];
    }
}
