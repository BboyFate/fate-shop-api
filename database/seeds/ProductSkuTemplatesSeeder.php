<?php

use App\Models\ProductSkuTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSkuTemplatesSeeder extends Seeder
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
                'value' => [
                    [
                        'name' => '颜色',
                        'is_showed' => true,
                        'attributes' => ['黑色', '白色', '绿色', '红色', '橘色']
                    ],
                    [
                        'name' => '尺码',
                        'is_showed' => true,
                        'attributes' => ['S', 'M', 'L', 'XL', 'XXL', 'XXXL']
                    ]
                ]
            ],
            [
                'name' => '鞋子模板',
                'value' => [
                    [
                        'name' => '颜色',
                        'is_showed' => true,
                        'attributes' => ['黑色', '白色']
                    ],
                    [
                        'name' => '尺码',
                        'is_showed' => true,
                        'attributes' => ['37','38','39','40','41','42','43','44','45']
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
        ProductSkuTemplate::query()->create($data);
    }
}
