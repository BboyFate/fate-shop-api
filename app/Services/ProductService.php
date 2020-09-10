<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function store(array $productData)
    {
        $product = \DB::transaction(function () use ($productData) {
            $product = new Product([
                'title'       => $productData['title'],
                'long_title'  => $productData['long_title'],
                'description' => $productData['description'],
                'on_sale'     => $productData['on_sale'],
                'type'        => $productData['type'],
                'image'       => $productData['image'],
            ]);

            $product->category()->associate($productData['category_id']);
            $product->save();

            if ($productData['type'] === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->create([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            if ($productData['skus']) {
                foreach ($productData['skus'] as $data) {
                    $sku = $product->skus()->create([
                        'name'        => $data['name'],
                        'description' => $data['description'],
                        'price'       => $data['price'],
                        'stock'       => $data['stock'],
                        'attributes'  => $data['attributes'],
                    ]);
                }
            }

            if ($productData['properties']) {
                foreach ($productData['properties'] as $data) {
                    $product->properties()->create([
                        'name'  => $data['name'],
                        'value' => $data['value'],
                    ]);
                }
            }

            return $product;
        });

        return $product;
    }

    public function update(Product $product, array $productData)
    {
        $product = \DB::transaction(function () use ($product, $productData) {
            $product->update([
                'title'       => $productData['title'],
                'long_title'  => $productData['long_title'],
                'description' => $productData['description'],
                'on_sale'     => $productData['on_sale'],
                'image'       => $productData['image'],
            ]);

            $product->category()->associate($productData['category_id']);

            if ($productData['type'] === Product::TYPE_CROWDFUNDING) {
                $product->crowdfunding()->update([
                    'target_amount' => $productData['target_amount'],
                    'end_at'        => $productData['end_at'],
                ]);
            }

            if ($productData['skus']) {
                foreach ($productData['skus'] as $data) {
                    $temp = [
                        'name'        => $data['name'],
                        'description' => $data['description'],
                        'price'       => $data['price'],
                        'stock'       => $data['stock'],
                        'attributes'  => $data['attributes'],
                    ];

                    if (isset($data['id'])) {
                        $product->skus()->where(['id' => $data['id']])->update($temp);
                    } else {
                        $product->skus()->create($temp);
                    }
                }
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

            return $product;
        });

        return $product;
    }
}
