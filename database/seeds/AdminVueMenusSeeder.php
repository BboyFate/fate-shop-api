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
                'name' => 'Auth',
                'path' => '/auth',
                'component' => 'Layout',
                'meta' => [
                    'icon' => 'lock',
                    'title' => '权限管理'
                ],
                'children' => [
                    [
                        'name' => 'Admin',
                        'path' => 'admin',
                        'component' => 'Admin',
                        'meta' => [
                            'title' => '管理员'
                        ],
                    ],
                    [
                        'name' => 'Role',
                        'path' => 'role',
                        'component' => 'Role',
                        'meta' => [
                            'title' => '角色'
                        ],
                    ],
                    [
                        'name' => 'Permission',
                        'path' => 'permission',
                        'component' => 'Permission',
                        'meta' => [
                            'title' => '权限'
                        ],
                    ],
                    [
                        'name' => 'VueMenu',
                        'path' => 'vue_menu',
                        'component' => 'VueMenu',
                        'meta' => [
                            'title' => 'Vue 菜单'
                        ],
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
