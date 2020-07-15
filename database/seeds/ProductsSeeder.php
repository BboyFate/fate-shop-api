<?php

use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = app(Faker\Generator::class);

        $products = factory(\App\Models\Product::class, 30)->create()->each(function ($product) {
            $product->skuAttributes()->createMany([
                ['name' => '套餐类型'],
                ['name' => '颜色'],
                ['name' => '内存'],
            ]);
        });

        foreach ($products as $product) {
            $skus = factory(\App\Models\ProductSku::class, 3)->create([
                'product_id' => $product->id,
                'attributes' => [
                    ["id" => $product->skuAttributes[0]->id, "value" => "套餐一"],
                    ["id" => $product->skuAttributes[1]->id, "value" => $faker->colorName],
                    ["id" => $product->skuAttributes[2]->id, "value" => $faker->randomElement(['256G', '128G', '64G'])]
                ]
            ]);

            $product->update(['price' => $skus->min('price')]);
        }
    }
}
