<?php

namespace App\Admin\Http\Validations\V1\Products;

use Illuminate\Support\Arr;

class AttributeTemplatesValidation
{
    protected function getRules() {
        return [
            'attributes'          => 'required|array',
            'attributes.*.name'   => 'required|string',
            'attributes.*.values' => 'required|array',
        ];
    }

    public function store()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|max:64|unique:product_attribute_templates',
            ]])
        ];
    }

    public function update()
    {
        return [
            'rules' => Arr::collapse([$this->getRules(), [
                'name' => 'required|string|max:64|unique:product_attribute_templates,name,' . request()->route('templateId'),
            ]])
        ];
    }
}
