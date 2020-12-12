<?php

namespace App\Services;

use App\Models\ProductCategory;

class ProductCategoryService
{
    /**
     * 获取所有分类列表
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getCategories()
    {
        $categories = ProductCategory::query()
            ->where('is_showed', true)
            ->orderByDesc('sorted')
            ->get();

        return $categories;
    }

    /**
     * 获取某个类目的所有子类目 ID
     *
     * @param $category
     * @return array|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getCategoryChildrenIds($category)
    {
        if (! $category) {
            return [];
        }

        return ProductCategory::query()
            ->where('path', 'like', $category->path . $category->id . '-%')
            ->get()
            ->map(function (ProductCategory $category) {
                return $category->id;
            });
    }

    /**
     * 循环更新某个类目的所有子类目 path 字段，会自动触发 saving 事件
     *
     * @param $category
     */
    public function updateChildrenPath($category)
    {
        if ($children = $category->children()) {
            $children->each(function ($child) {
                $child->update(['path' => '']);
                if ($children = $child->children()) {
                    $this->updateChildrenPath($child);
                }
            });
        }
    }

    /**
     * 获取商品类目
     *
     * @param $categories
     * @param int $parentId
     * @return array
     */
    public function generateCategoriesTree($categories, $parentId = 0) {
        if (! $categories) {
            return [];
        }

        return $categories
            ->where('parent_id', $parentId)
            ->map(function (ProductCategory $category) use ($categories) {
                $data = [
                    'id'        => $category->id,
                    'name'      => $category->name,
                    'parent_id' => $category->parent_id,
                ];

                if ($hasChildren = $categories->contains('parent_id', $category->id)) {
                    $data['children'] = $this->generateCategoriesTree($categories, $category->id)->toArray();
                }

                return $data;
            });
    }
}
