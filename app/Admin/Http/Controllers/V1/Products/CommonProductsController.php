<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Products\ProductAttributeTemplate;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;
use App\Services\ProductCategoryService;
use App\Admin\Http\Resources\Products\ProductResource;
use App\Admin\Http\Resources\Products\ProductDetailResource;

abstract class  CommonProductsController extends Controller
{
    abstract public function getProductType();

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('limit', 10);
        $builder = (new ProductSearchBuilder())->productType($this->getProductType())->paginate((int) $perPage, (int) $page);

        // 排序
        if ($order = $request->input('order', 'id_desc')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['id', 'price', 'sold_count', 'rating'])) {
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }

        // 是否上架
        $onSale = $request->input('on_sale');
        if ($onSale != '' && isset($onSale)) {
            $builder->onSale((boolean) $onSale);
        }

        // 商品类目
        if ($request->input('category_id') && $category = ProductCategory::query()->find($request->input('category_id'))) {
            $builder->category($category);
        }

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            // 将搜索词根据空格拆分成数组，并过滤掉空项
            $keywords = array_filter(explode(' ', $search));
            $builder->keywords($keywords);
        }

        // 只有有搜索词或类目筛选才用聚合
        if ($search || isset($category)) {
            $builder->aggregateProperties();
        }

        $result = app('es')->search($builder->getParams());

        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();

//        $products = QueryBuilder::for(Product::class)
//            ->allowedIncludes(['crowdfunding', 'category'])
//            ->byIds($productIds)
//            ->get();

        $products = Product::query()
            ->with(['category:id,name'])
            ->byIds($productIds)
            ->get();

        $properties = [];
        // 如果返回结果里有 aggregations 字段，说明做了分面搜索
        if (isset($result['aggregations'])) {
            $properties = collect($result['aggregations']['properties']['properties']['buckets'])
                ->map(function ($bucket) {
                    return [
                        'key' => $bucket['key'],
                        'values' => collect($bucket['value']['buckets'])->pluck('key')->all()
                    ];
                });
        }

        // 商品类型的总数量
        $totalParams = (new ProductSearchBuilder())
            ->productType($this->getProductType())
            ->getParams();
        $total = app('es')->count($totalParams);

        $pager = new LengthAwarePaginator($products, $total['count'], $perPage, $page, [
            'path' => route('api.v1.admin.products.index', false)
        ]);

        return $this->response->success(ProductResource::collection($pager));
    }

    public function store(Request $request, ProductService $service)
    {
        $this->validateRequest($request, 'requestValidation');

        $productData = $request->only([
            'title',
            'long_title',
            'number',
            'description',
            'on_sale',
            'category_id',
            'express_fee_id',
            'image',
            'attributes',
            'skus',
            'number',
        ]);
        $productData['type'] = $this->getProductType();
        $productData['banners'] = $request->input('banners', []);
        $productData['properties'] = $request->input('properties', []);

        $productData = array_merge($productData, $this->customForm($request));

        $product = $service->store($productData);

        $product->load('category', 'skus', 'attributes', 'crowdfunding', 'description');

        return $this->response->created(new ProductResource($product));
    }

    public function update(Request $request, $productId, ProductService $service)
    {
        $product = Product::query()->with(['category', 'skus', 'attributes', 'crowdfunding'])->findOrFail($productId);

        $this->validateRequest($request, 'requestValidation');

        $productData = $request->only([
            'title',
            'long_title',
            'number',
            'description',
            'on_sale',
            'category_id',
            'image',
            'attributes',
            'skus',
        ]);
        $productData['type'] = $this->getProductType();
        $productData['banners'] = $request->input('banners', []);
        $productData['properties'] = $request->input('properties', []);

        $productData = array_merge($productData, $this->customForm($request));

        $product = $service->update($product, $productData);

        $product->load('category', 'skus', 'attributes', 'crowdfunding', 'description');

        return $this->response->success(new ProductResource($product));
    }

    public function show($productId)
    {
        $product = QueryBuilder::for(Product::class)
            ->allowedIncludes(['category', 'skus', 'attributes', 'crowdfunding'])
            ->findOrFail($productId);

        return $this->response->success(new ProductResource($product));
    }

    abstract protected function customForm(Request $request);

    public function destroy($productId)
    {
        $product = Product::query()->findOrFail($productId);

        \DB::transaction(function () use ($product) {
            $product->delete();
            $product->skus()->delete();
        });

        return $this->response->noContent();
    }

    /**
     * 商品详情页
     *
     * @param $id
     * @param ProductCategoryService $productCategoryService
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($productId, ProductCategoryService $productCategoryService)
    {
        $product = QueryBuilder::for(Product::class)
            ->allowedIncludes(['category', 'skus', 'attributes', 'crowdfunding', 'description'])
            ->whereId($productId)
            ->first();

        $categories = $productCategoryService->generateCategoriesTree($productCategoryService->getCategories());
        $attributeTemplates = ProductAttributeTemplate::query()->get();

        $result = (new ProductDetailResource($product))
            ->setCategories($categories)
            ->setAttributeTemplates($attributeTemplates);

        return $this->response->success($result);
    }
}
