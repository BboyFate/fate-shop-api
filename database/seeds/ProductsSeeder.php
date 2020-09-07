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
        $products = factory(\App\Models\Product::class, 30)->create();

        foreach ($products as $product) {
            $attributes = $product->category->skuAttributes();
            $values = collect($this->getAttibuteRandomValues());
            $skuAttributes = [];
            foreach ($attributes as $attribute) {
                $temp = [
                    'id' => $attributes->id,
                    'value' => $values->pluck($attribute->name)->random(),
                ];
                $skuAttributes[] = $temp;
            }

            $skus = factory(\App\Models\ProductSku::class, 3)->create([
                'product_id' => $product->id,
                'attributes' => $skuAttributes
            ]);

            $product->update(['price' => $skus->min('price')]);
        }
    }

    protected function getAttibuteRandomValues()
    {
        return [
            '选择颜色' => ['白色', '黑色', '蓝色'],
            '选择版本' => ['版本一', '版本二'],
            '套餐' => ['套餐一', '套餐二', '套餐三'],
            '选择尺寸' => ['26寸', '36寸', '66寸'],
            '配置' => ['配置一', '配置二', '配置三'],
            '选择尺码' => ['26', '27', '28', '29', '30', '31', '32']
        ];
    }
}
