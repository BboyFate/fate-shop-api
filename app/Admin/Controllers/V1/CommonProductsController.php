<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Admin\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\ProductCategory;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;

abstract class CommonProductsController extends Controller
{
    abstract public function getProductType();

    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('limit', 10);
        $builder = (new ProductSearchBuilder())->productType($this->getProductType())->paginate((int) $perPage, (int) $page);

        // 排序
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
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
        $products = Product::query()->with(['crowdfunding', 'category'])->byIds($productIds)->get();

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
            'description',
            'on_sale',
            'category_id',
            'image',
            'sku_attributes',
            'skus',
        ]);
        $productData['type'] = $this->getProductType();
        $productData['banners'] = $request->input('banners', []);
        $productData['properties'] = $request->input('properties', []);

        $productData = array_merge($productData, $this->customForm($request));

        $product = $service->store($productData);

        $product->load('category', 'skus', 'skuAttributes', 'crowdfunding');

        return $this->response->created(new ProductResource($product));
    }

    public function update(Request $request, $id, ProductService $service)
    {
        $product = Product::query()->with(['category', 'skus', 'skuAttributes', 'crowdfunding'])->findOrFail($id);

        $this->validateRequest($request, 'requestValidation');

        $productData = $request->only([
            'title',
            'long_title',
            'description',
            'on_sale',
            'category_id',
            'image',
            'sku_attributes',
            'skus',
        ]);
        $productData['type'] = $this->getProductType();
        $productData['banners'] = $request->input('banners', []);
        $productData['properties'] = $request->input('properties', []);

        $productData = array_merge($productData, $this->customForm($request));

        $product = $service->update($product, $productData);

        $product->load('category', 'skus', 'skuAttributes');

        return $this->response->success(new ProductResource($product));
    }

    public function show($id)
    {
        $product = Product::query()->with(['category', 'skus', 'skuAttributes', 'crowdfunding'])->findOrFail($id);

        return $this->response->success(new ProductResource($product));
    }

    abstract protected function customForm(Request $request);

    public function destroy($id)
    {
        $product = Product::query()->findOrFail($id);
        $product->delete();

        return $this->response->noContent();
    }

    public function skuDestroy($id)
    {
        $sku = ProductSku::query()->findOrFail($id);
        $sku->delete();

        return $this->response->noContent();
    }

    public function skusDestroy(Request $request)
    {
        $this->validateRequest($request);

        ProductSku::destroy($request->input('sku_ids'));

        return $this->response->noContent();
    }

}
