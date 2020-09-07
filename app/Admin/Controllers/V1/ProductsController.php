<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Admin\Models\AdminImage;
use App\Admin\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSkuAttribute;
use App\SearchBuilders\ProductSearchBuilder;
use App\Services\ProductService;

class ProductsController extends Controller
{
    public function index(Request $request, ProductService $service)
    {
        $page = $request->input('page', 1);
        $perPage = 16;
        $builder = (new ProductSearchBuilder())->paginate($perPage, $page);
        $search = $request->input('search', '');
        $categoryId = $request->input('category_id');

        $builder = $service->search($builder, $search);
        $builder = $service->searchOrder($builder, $request->input('order', ''));
        $builder = $service->searchCategory($builder, $request->input('category_id'));
        $builder = $service->searchFilter($builder, $request->input('filters'));
        $builder = $service->searchOnSale($builder, $request->input('on_sale', ''));
        if ($categoryId && $category = ProductCategory::find($categoryId)) {
            $builder = $service->category($builder, $category);
        }

        // 只有有搜索词或类目筛选才用聚合
        if ($search || isset($category)) {
            $builder->aggregateProperties();
        }

        $result = app('es')->search($builder->getParams());

        $productIds = collect($result['hits']['hits'])->pluck('_id')->all();
        $products = Product::query()->byIds($productIds)->get();

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
            'path' => route('api.v1.admin.products.index', false)
        ]);

        return ProductResource::collection($pager);
    }

    public function store(Request $request, ProductService $service)
    {
        $this->validateRequest($request, $this->requestValidationRules('post', $request->input('category_id')));

        $image = AdminImage::query()->find($request->product_image_id);

        $productData = $request->only([
            'title',
            'long_title',
            'description',
            'on_sale',
            'category_id',
        ]);
        $productData['image'] = $image->path;
        $productData['type'] = Product::TYPE_NORMAL;
        $productData['skus'] = $request->input('skus');
        $productData['properties'] = $request->input('properties');

        $product = $service->store($productData);

        return new ProductResource($product);
    }

    protected function requestValidationRules($requestType, $categoryId)
    {
        $rules = [
            'title'                     => 'required',
            'long_title'                => 'required',
            'description'               => 'required',
            'on_sale'                   => 'required|boolean',
            'category_id'               => 'required|exists:product_categories,id',
            'product_image_id'          => 'required|exists:admin_images,id',
            'skus'                      => 'array',
            'skus.*.name'               => 'required',
            'skus.*.description'        => 'required',
            'skus.*.price'              => 'required|numeric|min:0.01',
            'skus.*.stock'              => 'required|integer|min:0',
            'skus.*.attributes'         => 'array',
            'skus.*.attributes.*.id'    => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($categoryId) {
                    $skuAttribute = ProductSkuAttribute::query()->find($value);
                    if (! $skuAttribute) {
                        $fail('不存在该商品 SKU 规格');
                        return;
                    }

                    if ($categoryId != $skuAttribute->product_category_id) {
                        $fail('规格和分类不对应');
                        return;
                    }
                }
            ],
            'skus.*.attributes.*.value' => 'required',
            'properties'                => 'array',
            'properties.*.name'         => 'required',
            'properties.*.value'        => 'required',
        ];

        return $rules;
    }

    public function update(Request $request, $id, ProductService $service)
    {
        $product = Product::query()->findOrFail($id);
        $this->validateRequest($request, $this->requestValidationRules('patch', $product->category_id));

        $image = AdminImage::query()->find($request->product_image_id);

        $productData = $request->only([
            'title',
            'long_title',
            'description',
            'on_sale',
            'category_id',
        ]);
        $productData['image'] = $image->path;
        $productData['skus'] = $request->input('skus');
        $productData['properties'] = $request->input('properties');

        $product = $service->update($product, $productData);

        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $product = Product::query()->findOrFail($id);
        $product->delete();

        return $this->response->noContent();
    }
}
