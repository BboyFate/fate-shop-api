<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Services\ProductCategoryService;
use App\Admin\Http\Resources\Products\ProductCategoryResource;
use App\Admin\Http\Resources\Products\ProductCategoryCollection;

class CategoriesController extends Controller
{
    public function index(Request $request, ProductCategoryService $productCategoryService)
    {
        $limit = $request->input('limit', 10);
        $builder = ProductCategory::query();

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->whereHas('children', function ($query) use ($like) {
                $query->where('name', 'like', $like);
            });
        }

        $categories = $builder->paginate($limit);
        $categoriesTree = $productCategoryService->generateCategoriesTree(ProductCategory::query()->get());

        $result = (new ProductCategoryCollection($categories))
            ->setCategoriesTree($categoriesTree);

        return $this->response->success($result);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $requestData = $request->only([
            'name',
            'parent_id',
            'image',
            'sorted',
            'is_showed',
        ]);

        $category = \DB::transaction(function () use ($requestData) {
            $category = new ProductCategory([
                'name'      => $requestData['name'],
                'image'     => $requestData['image'] ?? '',
                'sorted'    => $requestData['sorted'],
                'is_showed' => $requestData['is_showed'],
            ]);

            $category->save();

            if ($categoryParent = ProductCategory::query()->find($requestData['parent_id'])) {
                $category->parent()->associate($categoryParent);
            }

            return $category;
        });

        return $this->response->created(new ProductCategoryResource($category));
    }

    public function update(Request $request, $categoryId)
    {
        $category = ProductCategory::query()->findOrFail($categoryId);
        $this->validateRequest($request);

        $requestData = $request->only([
            'name',
            'parent_id',
            'image',
            'sorted',
            'is_showed',
        ]);

        $category = \DB::transaction(function () use ($category, $requestData) {
            $category->name = $requestData['name'];
            $category->image = $requestData['image'] ?? '';
            $category->sorted = $requestData['sorted'];
            $category->is_showed = $requestData['is_showed'];

            if ($categoryParent = ProductCategory::query()->find($requestData['parent_id'])) {
                $category->parent()->associate($categoryParent);
            }

            $category->save();

            (new ProductCategoryService)->updateChildrenPath($category);

            return $category;
        });

        return $this->response->success(new ProductCategoryResource($category));
    }

    public function destroy($categoryId, ProductCategoryService $productCategoryService)
    {
        $category = ProductCategory::query()->findOrFail($categoryId);

        $ids = $productCategoryService->getCategoryChildrenIds($category);
        $product = Product::query()->whereIn('category_id', $ids)->first();
        if ($product) {
            return $this->response->errorBadRequest('该分类下有商品');
        }

        $category->delete($ids);

        return $this->response->noContent();
    }

    /**
     * 类目格式化成有子类目项
     *
     * @param ProductCategoryService $productCategoryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoriesTree(ProductCategoryService $productCategoryService)
    {
        $categoriesTree = $productCategoryService->generateCategoriesTree(ProductCategory::query()->get());

        return $this->response->success($categoriesTree);
    }
}
