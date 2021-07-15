<?php

namespace App\Admin\Http\Resources\Products;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCategoryCollection extends ResourceCollection
{
    protected $categoriesTree = [];

    /**
     * collects 属性定义了资源类。
     *
     * @var string
     */
    public $collects = 'App\Admin\Http\Resources\Products\ProductCategoryResource';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data'           => $this->collection,
            'categoriesTree' => $this->categoriesTree,
        ];
    }

    /**
     * 设置返回的商品类目
     *
     * @param $categories
     * @return $this
     */
    public function setCategoriesTree($categories)
    {
        $this->categoriesTree = is_array($categories) ? $categories : $categories->toArray();

        return $this;
    }
}
