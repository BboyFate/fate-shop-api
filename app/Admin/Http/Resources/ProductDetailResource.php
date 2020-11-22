<?php

namespace App\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public $categories = [];
    public $attributeTemplates = [];

    public function toArray($request)
    {
        return [
            'product'             => new ProductResource($this->resource),
            'categories'          => $this->categories,
            'attribute_templates' => $this->attributeTemplates,
        ];
    }

    /**
     * 设置返回的商品类目
     *
     * @param $categories
     * @return $this
     */
    public function setCategories($categories)
    {
        $this->categories = is_array($categories) ? $categories : $categories->toArray();

        return $this;
    }

    /**
     * 设置返回的商品规格模板
     *
     * @param $categories
     * @return $this
     */
    public function setAttributeTemplates($templates)
    {
        $this->attributeTemplates = is_array($templates) ? $templates : $templates->toArray();

        return $this;
    }
}
