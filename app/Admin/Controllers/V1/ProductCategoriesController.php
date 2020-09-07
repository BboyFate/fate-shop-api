<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use App\Admin\Resources\ProductCategoryResource;
use App\Models\ProductCategory;

class ProductCategoriesController extends Controller
{
    public function index(Request $request)
    {
        $builder = ProductCategory::query();

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('province', 'like', $like)
                    ->orWhere('city', 'like', $like)
                    ->orWhere('district', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('contact_name', 'like', $like)
                    ->orWhere('contact_phone', 'like', $like);
            });
        }

        $users = $builder->paginate();

        return ProductCategoryResource::collection($users);
    }

    public function store(Request $request)
    {
        $isDirectory = $request->input('is_directory');

        $this->validateRequest($request, $this->storeRequestValidationRules($isDirectory));

        $name = $request->input('name');
        $categoryParent = ProductCategory::query()->find($request->input('parent_id', 0));
        $attributes = $request->input('attributes');

        $category = \DB::transaction(function () use ($name, $isDirectory, $categoryParent, $attributes) {
            $category = new ProductCategory([
                'name'         => $name,
                'is_directory' => $isDirectory
            ]);

            if ($categoryParent) {
                $category->parent()->associate($categoryParent);
            }

            $category->save();

            // 创建商品规格属性，用于多维度 SKU
            if ($attributes) {
                $data = [];
                foreach ($attributes as $attribute) {
                    $data[] = [
                        'name' => $attribute['name']
                    ];
                }

                $category->skuAttributes()->createMany($attributes);
            }

            return $category;
        });

        return new ProductCategoryResource($category);
    }

    protected function storeRequestValidationRules($isDirectory)
    {
        return [
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
        ];
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::query()->findOrFail($id);
        $this->validateRequest($request, $this->updateRequestValidationRules());

        $name = $request->input('name');
        $attributes = $request->input('attributes');

        $category = \DB::transaction(function () use ($category, $name, $attributes) {
            $category->update([
                'name' => $name,
            ]);

            if ($attributes) {
                // 更新商品规格属性，用于多维度 SKU
                foreach ($attributes as $attribute) {
                    $category->skuAttributes()->updateOrCreate(
                        ['id' => $attribute['id']],
                        ['name' => $attribute['name']]
                    );
                }
            }

            return $category;
        });

        return new ProductCategoryResource($category);
    }

    protected function updateRequestValidationRules()
    {
        return [
            'name'              => 'required|unique:product_categories,name',
            'attributes'        => 'array',
            'attributes.*.name' => 'filled',
        ];
    }

    public function destroy($id)
    {
        $category = ProductCategory::query()->findOrFail($id);
        $category->delete();

        return $this->response->noContent();
    }
}
