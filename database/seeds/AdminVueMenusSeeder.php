<?php

use Illuminate\Database\Seeder;
use App\Admin\Models\AdminVueMenu;

class AdminVueMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = [
            [
                'name' => 'Nested',
                'path' => '/nested',
                'redirect' => '/nested/menu1/menu1-1',
                'component' => 'nested',
                'meta' => [
                    'icon' => 'nested'
                ],
                'children' => [
                    [
                        'name' => 'Menu1',
                        'path' => 'menu1',
                        'redirect' => '/nested/menu1/menu1-1',
                        'component' => 'nested',
                        'meta' => [],
                    ],
                    [
                        'name' => 'Menu2',
                        'path' => 'menu2',
                        'redirect' => '/nested/menu1/menu1-2',
                        'component' => 'nested',
                        'meta' => [],
                    ],
                ]
            ]
        ];

        foreach ($menus as $data) {
            $this->createMenu($data);
        }
    }

    protected function createMenu($data, $parent = null)
    {
        $menu = new AdminVueMenu([
            'name'      => $data['name'],
            'path'      => $data['path'],
            'redirect'  => $data['redirect'],
            'meta'      => $data['meta'],
            'component' => $data['component'],
        ]);

        if (! is_null($parent)) {
            $menu->parent()->associate($parent);
        }
        $menu->save();

        // 如果有 children 字段并且 children 字段是一个数组
        if (isset($data['children']) && is_array($data['children'])) {
            // 遍历 children 字段
            foreach ($data['children'] as $child) {
                // 递归调用 createCategory 方法，第二个参数即为刚刚创建的类目
                $this->createMenu($child, $menu);
            }
        }
    }
}
