<?php

use App\Models\SystemImage;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductAttributeTemplate;

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
        $products = factory(Product::class, 30)->create();

        foreach ($products as $product) {
            $image = SystemImage::query()->inRandomOrder()->first();
            $template = ProductAttributeTemplate::query()->inRandomOrder()->first();

            // SKU 多规格
            $productSkuAttributesData = [];
            foreach ($template->attributes as $data) {
                $productSkuAttributesData[] = [
                    'name'   => $data['name'],
                    'values' => $data['values'],
                ];
            }
            $skuAttributes = $product->attributes()->createMany($productSkuAttributesData);

            $formatted = (new \App\Services\ProductService)->formatAttributes($skuAttributes);

            // SKU
            $skusData = [];
            foreach ($formatted as $attribute) {
                $attributesTemp = [];
                foreach ($attribute['attribute'] as $name => $value) {
                    $attributesTemp[] = [
                        'name' => $name,
                        'value' => $value,
                    ];
                }

                $skusData[] = [
                    'name'       => $faker->word,
                    'image'      => $image->path,
                    'price'      => $faker->randomNumber(4),
                    'stock'      => $faker->randomNumber(5),
                    'attributes' => $attributesTemp,
                ];
            }

            $skus = $product->skus()->createMany($skusData);

            $product->description()->create([
                'description' => $faker->sentence,
            ]);

            // 商品的价格更新为 SKU 中最低的价格
            $product->update(['price' => $skus->min('price')]);

            if ($product->type === Product::TYPE_CROWDFUNDING) {

                $product->crowdfunding()->create([
                    'target_amount' => $faker->randomNumber(6),
                    'total_amount'  => $faker->randomNumber(3),
                    'user_count'    => $faker->randomNumber(2),
                    'end_at'        => $faker->dateTimeBetween('now', '+5 days'),
                ]);
            }
        }
    }
}
