<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function store(array $productData)
    {
        $product = \DB::transaction(function () use ($productData) {
            $product = new Product([
                'title'        => $productData['title'],
                'long_title'   => $productData['long_title'],
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

            if ($productData['type'] === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->create([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            if ($productData['description']) {
                $product->description()->create([
                    'description' => $productData['description'],
                ]);
            }

            if ($productData['properties']) {
                foreach ($productData['properties'] as $data) {
                    $product->properties()->create([
                        'name'  => $data['name'],
                        'value' => $data['value'],
                    ]);
                }
            }

            foreach ($productData['skus'] as $data) {
                $product->skus()->create([
                    'name'       => $data['name'],
                    'image'      => $data['image'],
                    'price'      => $data['price'],
                    'stock'      => $data['stock'],
                    'attributes' => $data['attributes'],
                ]);
            }

            foreach ($productData['sku_attributes'] as $data) {
                $product->skuAttributes()->create([
                    'name'  => $data['name'],
                    'value' => $data['value'],
                ]);
            }

            $product->price = collect($productData['skus'])->min('price') ?: 0;

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
                'on_sale'    => $productData['on_sale'],
                'image'      => $productData['image'],
                'banners'    => $productData['banners'],
            ]);

            $product->category()->associate($productData['category_id']);

            // 众筹商品
            if ($product->type === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->update([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            if ($productData['description']) {
                $product->description()->update(['description' => $productData['description']]);
            }

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

            foreach ($productData['skus'] as $data) {
                $temp = [
                    'name'       => $data['name'],
                    'image'      => $data['image'],
                    'price'      => $data['price'],
                    'stock'      => $data['stock'],
                    'attributes' => $data['attributes'],
                ];

                if (isset($data['id'])) {
                    $product->skus()->where(['id' => $data['id']])->update($temp);
                } else {
                    $product->skus()->create($temp);
                }
            }

            foreach ($productData['sku_attributes'] as $data) {
                $product->skuAttributes()->updateOrCreate(
                    ['name' => $data['name']],
                    ['value' => $data['value']]
                );
            }

            return $product;
        });

        return $product;
    }

    public function formatAttributes($attributes)
    {
//        $attributes =  [
//            [
//                'name' => '颜色',
//                'value' => ['黑色', '白色']
//            ],
//            [
//                'name' => '尺码',
//                'value' => ['S', 'M', 'L']
//            ],
//            [
//                'name' => '年龄',
//                'value' => ['1', '2']
//            ]
//        ];

        $data = [];
        $results = [];
        $count = count($attributes);

        if ($count > 1) {
            for ($i = 0; $i < $count - 1; $i++) {
                if ($i == 0) {
                    $data = $attributes[$i]['value'];
                }

                foreach ($data as $v) {

                    foreach ($attributes[$i + 1]['value'] as $g) {
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
                foreach ($v['value'] as $kk => $vv) {
                    $dataArr[$kk] = $v['name'] . '_' . $vv;
                    $results[$kk]['attribute'][$v['name']] = $vv;
                }
            }
        }

        return $results;
    }
}
