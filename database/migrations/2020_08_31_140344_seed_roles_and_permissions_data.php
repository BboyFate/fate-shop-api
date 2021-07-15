<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Systems\SysPermission;

class SeedRolesAndPermissionsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        config(['auth.defaults.guard' => 'admin']);

        // 创建一个超级管理员
        $role = Role::query()->create([
            'name'        => config('app.super_admin_name'),
            'description' => '超级管理员'
        ]);

        foreach ($this->getPermissions() as $permission) {
            $this->createPermission($permission);
        }
    }

    protected function createPermission($data, $parent = null)
    {
        $permission = new SysPermission([
            'type'        => $data['type'],
            'name'        => $data['name'],
            'path'        => $data['path'],
            'meta'        => $data['meta'],
            'component'   => $data['component'],
        ]);

        if (isset($data['is_showed'])) {
            $permission->is_showed = $data['is_showed'];
        }

        if (! is_null($parent)) {
            $permission->parent()->associate($parent);
        }
        $permission->save();

        // 如果有 children 字段并且 children 字段是一个数组
        if (isset($data['children']) && is_array($data['children'])) {
            // 遍历 children 字段
            foreach ($data['children'] as $child) {
                // 递归调用 createCategory 方法，第二个参数即为刚刚创建的类目
                $this->createPermission($child, $permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 清空所有数据表数据
        $tableNames = config('permission.table_names');

        DB::table($tableNames['role_has_permissions'])->delete();
        DB::table($tableNames['model_has_roles'])->delete();
        DB::table($tableNames['model_has_permissions'])->delete();
        DB::table($tableNames['roles'])->delete();
        DB::table($tableNames['permissions'])->delete();
    }

    protected function getPermissions()
    {
        $permissions = [
            [
                'type'      => SysPermission::TYPE_DIRECTORY,
                'name'      => 'system',
                'path'      => '/systems',
                'component' => 'Layout',
                'meta'      => [
                    'title' => '系统管理',
                    'icon'  => 'system',
                ],
                'children'  => [
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'system.role.supervise',
                        'path'      => 'roles',
                        'component' => 'systems/roles/index',
                        'meta'      => [
                            'title' => '角色管理',
                            'icon'  => 'peoples',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'system.role.list',
                                'path'      => 'index',
                                'component' => 'systems/roles/index',
                                'is_showed' => false,
                                'meta'      => [
                                    'title' => '角色列表',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.role.store',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '添加角色',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.role.update',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '更新角色',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.role.destroy',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '删除角色',
                                ],
                            ],
                        ]
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'system.permission.supervise',
                        'path'      => 'permissions',
                        'component' => 'systems/permissions/index',
                        'meta'      => [
                            'title' => '权限管理',
                            'icon'  => 'tree-table',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'system.permission.list',
                                'path'      => 'index',
                                'component' => 'systems/permissions/index',
                                'is_showed' => false,
                                'meta'      => [
                                    'title' => '权限列表',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.permission.store',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '添加权限',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.permission.update',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '更新权限',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.permission.destroy',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '删除权限',
                                ],
                            ],
                        ]
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'system.dictionaryType.getDictionariesByType',
                        'path'      => 'dictionaries/types/:id(\\d+)',
                        'component' => 'systems/dictionaries/dictionaries-by-type',
                        'is_showed' => false,
                        'meta'      => [
                            'title'      => '数据字典',
                            'activeMenu' => '/systems/dictionaries/types',
                        ],
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'system.dictionaryType',
                        'path'      => 'dictionaries/types',
                        'component' => 'systems/dictionaries/types',
                        'meta'      => [
                            'title' => '字典管理',
                            'icon'  => 'dict',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionaryType.store',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '添加字典类型',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionaryType.update',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '更新字典类型',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionaryType.destroy',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '删除字典类型',
                                ],
                            ],
                        ]
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'system.dictionary',
                        'path'      => 'dictionaries',
                        'component' => 'systems/dictionaries/dictionaries',
                        'is_showed' => false,
                        'meta'      => [
                            'title' => '数据字典',
                            'icon'  => 'dict',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionary.store',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '添加字典',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionary.update',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '更新字典',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'system.dictionary.destroy',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '删除字典',
                                ],
                            ],
                        ]
                    ],
                ]
            ],
            [
                'type'      => SysPermission::TYPE_DIRECTORY,
                'name'      => 'product',
                'path'      => '/products',
                'component' => 'Layout',
                'meta'      => [
                    'title' => '商品管理',
                    'icon'  => 'el-icon-s-goods',
                ],
                'children'  => [
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'product.normal.supervise',
                        'path'      => 'products',
                        'component' => 'ParentView',
                        'meta'      => [
                            'title' => '商品管理',
                            'icon'  => '',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'product.normal.list',
                                'path'      => 'normals',
                                'component' => 'products/products/normals/index',
                                'meta'      => [
                                    'title' => '普通商品',
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'product.normal.store',
                                'path'      => 'create',
                                'component' => 'products/products/normals/create',
                                'is_showed' => false,
                                'meta'      => [
                                    'title' => '添加普通商品',
                                    'activeMenu' => '/products/products/normals'
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'product.normal.update',
                                'path'      => ':id(\\d+)',
                                'component' => 'products/products/normals/edit',
                                'is_showed' => false,
                                'meta'      => [
                                    'title' => '编辑普通商品',
                                    'activeMenu' => '/products/products/normals'
                                ],
                            ],
                            [
                                'type'      => SysPermission::TYPE_BTN,
                                'name'      => 'product.normal.destroy',
                                'path'      => '',
                                'component' => '',
                                'meta'      => [
                                    'title' => '删除普通商品',
                                ],
                            ],
                        ]
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'product.setting.supervise',
                        'path'      => 'settings',
                        'component' => 'ParentView',
                        'meta'      => [
                            'title' => '商品配置',
                            'icon'  => '',
                        ],
                        'children'  => [
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'product.category.list',
                                'path'      => 'categories',
                                'component' => 'products/categories/index',
                                'meta'      => [
                                    'title' => '商品类目',
                                ],
                                'children' => [
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.category.store',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '添加类目',
                                        ],
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.category.update',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '编辑类目',
                                        ],
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.category.destroy',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '删除类目',
                                        ],
                                    ],
                                ]
                            ],
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'product.attribute.list',
                                'path'      => 'attributes',
                                'component' => 'products/attributes/index',
                                'meta'      => [
                                    'title' => '规格管理',
                                ],
                                'children' => [
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.attribute.store',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '添加规格',
                                        ],
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.attribute.update',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '编辑规格',
                                        ],
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_BTN,
                                        'name'      => 'product.attribute.destroy',
                                        'path'      => '',
                                        'component' => '',
                                        'meta'      => [
                                            'title' => '删除规格',
                                        ],
                                    ],
                                ]
                            ],
                            [
                                'type'      => SysPermission::TYPE_MENU,
                                'name'      => 'express',
                                'path'      => 'expresses',
                                'component' => 'ParentView',
                                'meta'      => [
                                    'title' => '快递运费',
                                ],
                                'children' => [
                                    [
                                        'type'      => SysPermission::TYPE_MENU,
                                        'name'      => 'express.company.list',
                                        'path'      => 'companies',
                                        'component' => 'products/expresses/companies/index',
                                        'meta'      => [
                                            'title' => '物流公司',
                                        ],
                                        'children' => [
                                            [
                                                'type'      => SysPermission::TYPE_MENU,
                                                'name'      => 'express.company.store',
                                                'path'      => 'create',
                                                'component' => 'products/expresses/companies/create',
                                                'is_showed' => false,
                                                'meta'      => [
                                                    'title' => '添加物流公司',
                                                    'activeMenu' => '/products/settings/expresses/companies',
                                                ],
                                            ],
                                            [
                                                'type'      => SysPermission::TYPE_BTN,
                                                'name'      => 'express.company.update',
                                                'path'      => '',
                                                'component' => '',
                                                'meta'      => [
                                                    'title' => '编辑物流公司',
                                                ],
                                            ],
                                            [
                                                'type'      => SysPermission::TYPE_BTN,
                                                'name'      => 'express.company.destroy',
                                                'path'      => '',
                                                'component' => '',
                                                'meta'      => [
                                                    'title' => '删除物流公司',
                                                ],
                                            ],
                                        ]
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_MENU,
                                        'name'      => 'express.fee.list',
                                        'path'      => 'fees',
                                        'component' => 'products/expresses/fees/index',
                                        'meta'      => [
                                            'title' => '运费模板',
                                        ],
                                        'children' => [
                                            [
                                                'type'      => SysPermission::TYPE_BTN,
                                                'name'      => 'express.fee.destroy',
                                                'path'      => '',
                                                'component' => '',
                                                'meta'      => [
                                                    'title' => '删除运费模板',
                                                ],
                                            ],
                                        ]
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_MENU,
                                        'name'      => 'express.fee.store',
                                        'path'      => 'fees/create',
                                        'component' => 'products/expresses/fees/create',
                                        'is_showed' => false,
                                        'meta'      => [
                                            'title' => '添加运费模板',
                                            'activeMenu' => '/products/settings/expresses/fees',
                                        ],
                                    ],
                                    [
                                        'type'      => SysPermission::TYPE_MENU,
                                        'name'      => 'express.fee.update',
                                        'path'      => 'fees/:id(\\d+)',
                                        'component' => 'products/expresses/fees/edit',
                                        'is_showed' => false,
                                        'meta'      => [
                                            'title' => '编辑运费模板',
                                            'activeMenu' => '/products/settings/expresses/fees',
                                        ],
                                    ],
                                ]
                            ],
                        ]
                    ],
                ]
            ],
            [
                'type'      => SysPermission::TYPE_DIRECTORY,
                'name'      => 'order',
                'path'      => '/orders',
                'component' => 'Layout',
                'meta'      => [
                    'title' => '订单管理',
                    'icon'  => 'el-icon-s-goods',
                ],
                'children' => [
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'order.list',
                        'path'      => 'orders',
                        'component' => 'orders/orders/index',
                        'meta'      => [
                            'title' => '订单列表',
                            'icon'  => '',
                        ],
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'order.show',
                        'path'      => 'orders/:id(\\d+)',
                        'component' => 'orders/orders/detail',
                        'is_showed' => false,
                        'meta'      => [
                            'title'      => '订单详情',
                            'activeMenu' => '/orders/orders',
                        ],
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'order.refund.list',
                        'path'      => 'refunds',
                        'component' => 'orders/refunds/index',
                        'meta'      => [
                            'title' => '退款记录',
                            'icon'  => '',
                        ],
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'order.refund.show',
                        'path'      => 'refunds/:id(\\d+)',
                        'component' => 'orders/refunds/detail',
                        'is_showed' => false,
                        'meta'      => [
                            'title'      => '退款详情',
                            'activeMenu' => '/orders/refunds',
                        ],
                    ],
                ]
            ],
            [
                'type'      => SysPermission::TYPE_DIRECTORY,
                'name'      => 'user',
                'path'      => '/users',
                'component' => 'Layout',
                'meta'      => [
                    'title' => '会员管理',
                    'icon'  => 'el-icon-user',
                ],
                'children' => [
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'user.list',
                        'path'      => 'users',
                        'component' => 'users/users/index',
                        'meta'      => [
                            'title' => '会员',
                            'icon'  => '',
                        ],
                    ],
                    [
                        'type'      => SysPermission::TYPE_MENU,
                        'name'      => 'user.show',
                        'path'      => 'users/:id(\\d+)',
                        'component' => 'users/users/detail',
                        'is_showed' => false,
                        'meta'      => [
                            'title'      => '会员详情',
                            'activeMenu' => '/users/users',
                        ],
                    ],
                ]
            ]
        ];

        return $permissions;
    }
}
