<?php

use Illuminate\Database\Seeder;
use App\Models\AdminVueMenu;

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
                'is_showed' => true,
                'meta' => [
                    'icon' => 'lock',
                    'title' => '权限管理'
                ],
                'children' => [
                    [
                        'name' => 'Admin',
                        'path' => 'admin',
                        'component' => 'Admin',
                        'is_showed' => true,
                        'meta' => [
                            'title' => '管理员'
                        ],
                    ],
                    [
                        'name' => 'Role',
                        'path' => 'role',
                        'component' => 'Role',
                        'is_showed' => true,
                        'meta' => [
                            'title' => '角色'
                        ],
                    ],
                    [
                        'name' => 'Permission',
                        'path' => 'permission',
                        'component' => 'Permission',
                        'is_showed' => true,
                        'meta' => [
                            'title' => '权限'
                        ],
                    ],
                    [
                        'name' => 'VueMenu',
                        'path' => 'vue_menu',
                        'component' => 'VueMenu',
                        'is_showed' => true,
                        'meta' => [
                            'title' => 'Vue 菜单'
                        ],
                    ],
                ]
            ],
            [
                'name' => 'OrderManage',
                'path' => '/order',
                'component' => 'Layout',
                'is_showed' => true,
                'meta' => [
                    'icon' => '',
                    'title' => ''
                ],
                'children' => [
                    [
                        'name' => 'Order',
                        'path' => 'order',
                        'component' => 'Order',
                        'is_showed' => true,
                        'meta' => [
                            'icon' => 'el-icon-s-order',
                            'title' => '订单管理',
                        ],
                    ],
                ]
            ],
            [
                'name' => 'ProductManage',
                'path' => '/product',
                'component' => 'Layout',
                'is_showed' => true,
                'meta' => [
                    'icon' => 'el-icon-s-goods',
                    'title' => '商品管理'
                ],
                'children' => [
                    [
                        'name' => 'Product',
                        'path' => '',
                        'component' => 'Product',
                        'is_showed' => true,
                        'meta' => [
                            'icon' => '',
                            'title' => '普通商品',
                        ],
                    ],
                    [
                        'name' => 'EditProduct',
                        'path' => '/product/edit/:id(\\d+)',
                        'component' => 'EditProduct',
                        'is_showed' => false,
                        'meta' => [
                            'icon' => '',
                            'title' => '编辑普通商品',
                            'activeMenu' => '/product',
                        ],
                    ],
                    [
                        'name' => 'CreateProduct',
                        'path' => '/product/create',
                        'component' => 'CreateProduct',
                        'is_showed' => false,
                        'meta' => [
                            'icon' => '',
                            'title' => '新建普通商品',
                            'activeMenu' => '/product',
                        ],
                    ],
                    [
                        'name' => 'CrowdfundingProduct',
                        'path' => '/product/crowdfunding',
                        'component' => 'CrowdfundingProduct',
                        'is_showed' => true,
                        'meta' => [
                            'icon' => '',
                            'title' => '众筹商品',
                        ],
                    ],
                    [
                        'name' => 'EditCrowdfundingProduct',
                        'path' => '/product/crowdfunding/edit/:id(\\d+)',
                        'component' => 'EditCrowdfundingProduct',
                        'is_showed' => false,
                        'meta' => [
                            'icon' => '',
                            'title' => '编辑众筹商品',
                            'activeMenu' => '/product/crowdfunding',
                        ],
                    ],
                    [
                        'name' => 'CreateCrowdfundingProduct',
                        'path' => '/product/crowdfunding/create',
                        'component' => 'CreateCrowdfundingProduct',
                        'is_showed' => false,
                        'meta' => [
                            'icon' => '',
                            'title' => '新建众筹商品',
                            'activeMenu' => '/product/crowdfunding',
                        ],
                    ],
                    [
                        'name' => 'ProductCategory',
                        'path' => '/product/category',
                        'component' => 'ProductCategory',
                        'is_showed' => true,
                        'meta' => [
                            'icon' => '',
                            'title' => '商品类目',
                        ],
                    ],
                    [
                        'name' => 'ProductAttributeTemplate',
                        'path' => '/product/attribute_template',
                        'component' => 'ProductAttributeTemplate',
                        'is_showed' => true,
                        'meta' => [
                            'icon' => '',
                            'title' => '规格模板',
                        ],
                    ],
                ]
            ],
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
            'is_showed' => $data['is_showed'],
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
