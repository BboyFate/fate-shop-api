<?php

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;

class ProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'name'     => '手机配件',
                'children' => [
                    ['name' => '手机壳'],
                    ['name' => '存储卡'],
                    ['name' => '数据线'],
                    ['name' => '充电器'],
                    [
                        'name'     => '耳机',
                        'children' => [
                            ['name' => '有线耳机'],
                            ['name' => '蓝牙耳机'],
                        ],
                    ],
                ],
            ],
            [
                'name'     => '电脑配件',
                'children' => [
                    ['name' => '显示器'],
                    ['name' => '显卡'],
                    ['name' => '内存'],
                    ['name' => 'CPU'],
                    ['name' => '主板'],
                    ['name' => '硬盘'],
                ],
            ],
            [
                'name'     => '电脑',
                'children' => [
                    ['name' => '笔记本'],
                    ['name' => '台式机'],
                    ['name' => '平板电脑'],
                ],
            ],
            [
                'name'     => '手机通讯',
                'children' => [
                    ['name' => '手机'],
                    ['name' => '游戏手机'],
                    ['name' => '5G手机'],
                ],
            ],
        ];

        foreach ($categories as $data) {
            $this->createCategory($data);
        }
    }

    protected function createCategory($data, $parent = null)
    {
        // 创建一个新的类目对象
        $category = new ProductCategory(['name' => $data['name']]);
        // 如果有 children 字段则代表这是一个父类目
        $category->is_directory = isset($data['children']);
        // 如果有传入 $parent 参数，代表有父类目
        if (! is_null($parent)) {
            $category->parent()->associate($parent);
        }
        //  保存到数据库
        $category->save();

        if ($attributesData = collect($this->getAttributes())->only($category->name)->values()) {
            $attributes = [];
            foreach ($attributesData as $attribute) {
                foreach ($attribute as $value) {
                    $temp = [
                        'name' => $value
                    ];
                    $attributes[] = $temp;
                }
            }

            $category->skuAttributes()->createMany($attributes);
        }

        // 如果有 children 字段并且 children 字段是一个数组
        if (isset($data['children']) && is_array($data['children'])) {
            // 遍历 children 字段
            foreach ($data['children'] as $child) {
                // 递归调用 createCategory 方法，第二个参数即为刚刚创建的类目
                $this->createCategory($child, $category);
            }
        }
    }

    protected function getAttributes()
    {
        return [
            '手机配件' => [
                '套装', '选择颜色'
            ],
            '手机壳' => [
                '选择颜色', '选择版本'
            ],
            '存储卡' => [
                '选择颜色'
            ],
            '数据线' => [
                '选择颜色', '选择版本'
            ],
            '充电器' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '耳机' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '有线耳机' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '蓝牙耳机' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '电脑配件' => [
                '选择颜色', '选择版本', '套餐', '选择尺寸'
            ],
            '显卡' => [
                '选择型号', '选择版本', '套餐', '选择尺寸'
            ],
            '显示器' => [
                '选择尺寸'
            ],
            '电脑' => [
                '选择颜色', '选择版本', '套餐', '选择尺寸', '配置'
            ],
            '笔记本' => [
                '选择颜色', '选择版本', '套餐', '选择尺寸', '配置'
            ],
            '台式机' => [
                '选择颜色', '选择版本', '套餐', '选择尺寸', '配置'
            ],
            '平板电脑' => [
                '选择颜色', '选择版本', '套餐', '选择尺寸', '配置'
            ],
            '手机通讯' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '手机' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '游戏手机' => [
                '选择颜色', '选择版本', '套餐'
            ],
            '5G手机' => [
                '选择颜色', '选择版本', '套餐'
            ],
        ];
    }
}
