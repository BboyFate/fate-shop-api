<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Product;
use App\SearchBuilders\ProductSearchBuilder;
use App\Http\Resources\ProductResource;

class ProductsController extends Controller
{
    /**
     * 商品列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = 16;
        $builder = (new ProductSearchBuilder())->onSale()->paginate($perPage, $page);

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

        // 商品类目
        if ($request->input('category_id') && $category = ProductCategory::find($request->input('category_id'))) {
            $builder->category($category);
        }

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            // 将搜索词根据空格拆分成数组，并过滤掉空项
            $keywords = array_filter(explode(' ', $search));
            $builder->keywords($keywords);
        }

        // 按商品属性筛选，例如：?filters=传输类型:DDR4|内存容量:32GB
        if ($filterString = $request->input('filters')) {
            $filterArray = explode('|', $filterString);
            foreach ($filterArray as $filter) {
                list($name, $value) = explode(':', $filter);
                $builder->propertyFilter($name, $value);
            }
        }

        // 只有有搜索词或类目筛选才用聚合
        if ($search || isset($category)) {
            $builder->aggregateProperties();
        }

        $result = app('es')->search($builder->getParams());

        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        $products = Product::query()
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

        $pager = new LengthAwarePaginator($products, $result['hits']['total']['value'], $perPage, $page, [
            'path' => route('api.v1.products.index', false)
        ]);

        return ProductResource::collection($pager);
    }

    /**
     * 商品详情
     *
     * @param $id
     * @return ProductResource|void
     */
    public function show($id)
    {
        $product = Product::query()->findOrFail($id);

        if (! $product->on_sale) {
            return $this->response->errorForbidden('商品已下架');
        }

        return new ProductResource($product);
    }

    /**
     * 收藏商品列表
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate();

        return ProductResource::collection($products);
    }

    /**
     * 收藏商品
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function favor(Request $request, $id)
    {
        $product = Product::query()->findOrFail($id);
        $user = $request->user();

        if ($user->favoriteProducts()->find($product->id)) {
            return $this->response->noContent();
        }

        $user->favoriteProducts()->attach($product);

        return $this->response->created();
    }

    /**
     * 取消收藏商品
     *
     * @param Product $product
     * @param Request $request
     * @return array
     */
    public function disfavor(Request $request, $id)
    {
        $product = Product::query()->findOrFail($id);
        $request->user()->favoriteProducts()->detach($product);

        return $this->response->noContent();
    }
}
