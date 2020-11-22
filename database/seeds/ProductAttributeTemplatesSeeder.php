<?php

use Illuminate\Database\Seeder;
use App\Models\ProductAttributeTemplate;

class ProductAttributeTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            [
                'name' => '衣服模板',
                'attributes' => [
                    [
                        'name' => '颜色',
                        'values' => ['黑色', '白色', '绿色', '红色', '橘色']
                    ],
                    [
                        'name' => '尺码',
                        'values' => ['S', 'M', 'L', 'XL', 'XXL', 'XXXL']
                    ]
                ]
            ],
            [
                'name' => '鞋子模板',
                'attributes' => [
                    [
                        'name' => '颜色',
                        'values' => ['黑色', '白色']
                    ],
                    [
                        'name' => '尺码',
                        'values' => ['37','38','39','40','41','42','43','44','45']
                    ]
                ]
            ],
        ];

        foreach ($templates as $data) {
            $this->createTemplate($data);
        }
    }

    public function createTemplate($data)
    {
        ProductAttributeTemplate::query()->create($data);
    }
}
