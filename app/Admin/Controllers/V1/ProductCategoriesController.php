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

        return $this->response->success(ProductCategoryResource::collection($users));
    }

    /**
     * 搜索一些分类
     */
    public function some(Request $request)
    {
        $search = $request->input('search');
        $result = ProductCategory::query()
            // 通过 is_directory 参数来控制
            ->where('is_directory', boolval($request->input('is_directory', true)))
            ->where('name', 'like', '%'.$search.'%')
            ->get();

        // 把查询出来的结果重新组装成 Laravel-Admin 需要的格式
        $result = $result->map(function (ProductCategory $category) {
            return ['id' => $category->id, 'name' => $category->full_name];
        });

        return $this->response->success($result);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

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

        return $this->response->created(new ProductCategoryResource($category));
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::query()->findOrFail($id);
        $this->validateRequest($request);

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

        return $this->response->success(new ProductCategoryResource($category));
    }

    public function destroy($id)
    {
        $category = ProductCategory::query()->findOrFail($id);
        $category->delete();

        return $this->response->noContent();
    }
}
