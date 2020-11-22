<?php

namespace App\Services;

use App\Admin\Http\Resources\ProductCategoryResource;
use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    public function store(array $productData)
    {
        $product = \DB::transaction(function () use ($productData) {
            $product = new Product([
                'title'        => $productData['title'],
                'long_title'   => $productData['long_title'],
                'number'       => $productData['number'] ?? '',
                'on_sale'      => $productData['on_sale'],
                'type'         => $productData['type'],
                'image'        => $productData['image'],
                'banners'      => $productData['banners'],
                'rating'       => 0,
                'review_count' => 0,
                'sold_count'   => 0,
            ]);

            $product->category()->associate($productData['category_id']);
            $product->save();

            // 众筹商品
            if ($productData['type'] === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->create([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            // 商品描述
            if ($productData['description']) {
                $product->description()->create([
                    'description' => $productData['description'],
                ]);
            }

            // 商品属性
            if ($productData['properties']) {
                foreach ($productData['properties'] as $data) {
                    $product->properties()->create([
                        'name'  => $data['name'],
                        'value' => $data['value'],
                    ]);
                }
            }

            // 商品 SKU
            foreach ($productData['skus'] as $data) {
                $product->skus()->create([
                    'name'       => $data['name'],
                    'image'      => $data['image'],
                    'price'      => $data['price'],
                    'stock'      => $data['stock'],
                    'attributes' => $data['attributes'],
                ]);
            }

            // 商品规格
            foreach ($productData['attributes'] as $productAttribute) {
                $product->attributes()->create([
                    'name'  => $productAttribute['name'],
                    'values' => $productAttribute['values'],
                ]);
            }

            // 商品价格更新为 SKU 之中最小的价格
            $product->update(['price' => collect($productData['skus'])->min('price') ?: 0]);

            return $product;
        });

        return $product;
    }

    public function update(Product $product, array $productData)
    {
        $product = \DB::transaction(function () use ($product, $productData) {
            $product->update([
                'title'      => $productData['title'],
                'long_title' => $productData['long_title'],
                'number'     => $productData['number'] ?? '',
                'on_sale'    => $productData['on_sale'],
                'image'      => $productData['image'],
                'banners'    => $productData['banners'],
            ]);

            // 众筹商品
            if ($product->type === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->update([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            // 商品分类
            $product->category()->associate($productData['category_id']);

            // 商品详情
            if ($productData['description']) {
                $product->description()->update(['description' => $productData['description']]);
            }

            // 商品属性
            if ($productData['properties']) {
                foreach ($productData['properties'] as $data) {
                    $temp = [
                        'name'  => $data['name'],
                        'value' => $data['value'],
                    ];

                    if (isset($data['id'])) {
                        $product->properties()->where(['id' => $data['id']])->update($temp);
                    } else {
                        $product->properties()->create($temp);
                    }
                }
            }

            // 商品 SKU
            $newSkuIds = [];
            foreach ($productData['skus'] as $skuData) {
                $temp = [
                    'name'       => $skuData['name'],
                    'image'      => $skuData['image'],
                    'price'      => $skuData['price'],
                    'stock'      => $skuData['stock'],
                    'attributes' => $skuData['attributes'],
                ];

                if (isset($skuData['id'])) {
                    $sku = $product->skus()->where(['id' => $skuData['id']])->firstOrFail();
                    $sku->update($temp);
                } else {
                    $sku = $product->skus()->create($temp);
                }
                $newSkuIds[] = $sku->id;
            }
            $product->skus()->whereNotIn('id', $newSkuIds)->delete();

            // 商品规格
            foreach ($productData['attributes'] as $productAttribute) {
                $product->attributes()->updateOrCreate(
                    ['name' => $productAttribute['name']],
                    ['values' => $productAttribute['values']]
                );
            }

            $product->update(['price' => collect($productData['skus'])->min('price') ?: 0]);

            return $product;
        });

        return $product;
    }

    public function formatAttributes($attributes)
    {
//        $attributes =  [
//            [
//                'name' => '颜色',
//                'values' => ['黑色', '白色']
//            ],
//            [
//                'name' => '尺码',
//                'values' => ['S', 'M', 'L']
//            ],
//        ];

        $data = [];
        $results = [];
        $count = count($attributes);

        if ($count > 1) {
            for ($i = 0; $i < $count - 1; $i++) {
                if ($i == 0) {
                    $data = $attributes[$i]['values'];
                }

                foreach ($data as $v) {

                    foreach ($attributes[$i + 1]['values'] as $g) {
                        // 拼接示例： 颜色_黑色-尺码_S
                        // 如果是第一个，返回名称『颜色』；如果不是第一个，返回当前循环的名称和下一个名称
                        $rep2 = ($i != 0 ? '' : $attributes[$i]['name'] . '_') . $v . '-' . $attributes[$i + 1]['name'] . '_' . $g;

                        $tmp[] = $rep2;

                        // 处理真正对应的结果
                        if ($i == $count - 2) {
                            foreach (explode('-', $rep2) as $k => $h) {
                                // 例如： 『颜色_黑色』切割成数组
                                $explodeArr = explode('_', $h);

                                $result['attribute'][$explodeArr[0]] = $explodeArr[1];
                            }

                            if($count == count($result['attribute'])) {
                                $results[] = $result;
                            }
                        }
                    }
                }

                $data = isset($tmp) ? $tmp : [];
            }
        } else {
            $dataArr = [];
            foreach ($attributes as $k => $v) {
                foreach ($v['values'] as $kk => $vv) {
                    $dataArr[$kk] = $v['name'] . '_' . $vv;
                    $results[$kk]['attribute'][$v['name']] = $vv;
                }
            }
        }

        return $results;
    }

    /**
     * 获取商品类目
     *
     * @param $categories
     * @param null $parentId
     * @return array
     */
    function generateCategoryTree($categories, $parentId = null) {
        if (! $categories) {
            return [];
        }

        return $categories
            ->where('parent_id', $parentId)
            ->map(function (ProductCategory $category) use ($categories) {
                $data = new ProductCategoryResource($category);

                if ($hasChildren = $categories->contains('parent_id', $category->id)) {
                    $data['children'] = $this->generateCategoryTree($categories, $category->id)->toArray();
                }

                return $data;
            });
    }
}
