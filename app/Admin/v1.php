<?php

$router->group([
    'prefix' => 'api/v1/admin',
    'namespace' => 'V1',
    'middleware' => [
        'accept_header',
        'admin_guard',
    ],
    'as' => 'api.v1.admin'
], function () use ($router) {
    /**
     * 需要登录才能访问的 API
     */
    $router->group([
        'middleware' => [
            'auth_refresh',
        ]
    ], function () use ($router) {
        /**
         * 当前登录的管理员
         */
        // 个人信息
        $router->get('user', ['uses' => 'MeController@me', 'as' => 'user.me']);
        // 更新个人信息
        $router->patch('user', ['uses' => 'MeController@meUpdate', 'as' => 'user.update']);
        // 有权限的菜单列表
        $router->get('user/menus', ['uses' => 'MeController@menus', 'as' => 'user.menus']);

        /**
         * 系统数据管理
         */
        $router->group([
            'namespace' => 'Systems',
            'as' => 'systems'
        ], function ($router) {

            /**
             * 系统字典
             */
            $router->get('systems/dictionaries', ['uses' => 'DictionariesController@index', 'as' => 'dictionaries.index']);
            $router->get('systems/dictionaries/{dictionaryId:[0-9]+}', ['uses' => 'DictionariesController@show', 'as' => 'dictionaries.show']);
            $router->get('systems/dictionaries/types', ['uses' => 'DictionaryTypesController@index', 'as' => 'dictionaries.types.index']);
            $router->get('systems/dictionaries/types/{typeId:[0-9]+}', ['uses' => 'DictionaryTypesController@show', 'as' => 'dictionaries.types.show']);
            $router->get('systems/dictionaries/types/filter_type', ['uses' => 'DictionaryTypesController@getDictionariesFilterType', 'as' => 'dictionaries.types.getDictionariesFilterType']);
            $router->get('systems/dictionaries/types/filter_types', ['uses' => 'DictionaryTypesController@getDictionariesFilterTypes', 'as' => 'dictionaries.types.getDictionariesFilterTypes']);

            /**
             * 区域地址
             */
            $router->get('systems/areas/provinces', ['uses' => 'AreasController@getProvinces', 'as' => 'areas.provinces']);
            $router->get('systems/areas/provinces/{provinceCode:[0-9]+}/cities', ['uses' => 'AreasController@getCities', 'as' => 'areas.cities']);
            $router->get('systems/areas/cities/{cityCode:[0-9]+}/districts', ['uses' => 'AreasController@getDistricts', 'as' => 'areas.districts']);
        });

        /**
         * 需要验证权限的 API
         *
         */
        $router->group([
            'middleware' => [
                'admin_check_permissions'
            ]
        ], function () use ($router) {
            /**
             * 图片
             */
            $router->get('images', ['uses' => 'SystemImagesController@index', 'as' => 'images.index']);
            $router->post('images', ['uses' => 'SystemImagesController@store', 'as' => 'images.store']);
            $router->delete('images/{image:[0-9]+}', [ 'uses' => 'SystemImagesController@destroy', 'as' => 'images.store']);
            $router->post('images/delete', ['uses' => 'SystemImagesController@imagesDestroy', 'as' => 'images.images_destroy']);

            $router->group([
                'namespace' => 'Systems',
                'as' => 'systems'
            ], function ($router) {
                /**
                 * 菜单
                 */
                $router->get('systems/menus', ['uses' => 'MenusController@index', 'as' => 'menus.index', 'middleware' => 'permission:system.menu.list']);
                $router->get('systems/menus/{menuId:[0-9]+}', ['uses' => 'MenusController@show', 'as' => 'menus.show', 'middleware' => 'permission:system.menu.show']);
                $router->post('systems/menus', ['uses' => 'MenusController@store', 'as' => 'menus.store', 'middleware' => 'permission:system.menu.store']);
                $router->patch('systems/menus/{menuId:[0-9]+}', ['uses' => 'MenusController@update', 'as' => 'menus.update', 'middleware' => 'permission:system.menu.update']);
                $router->delete('systems/menus/{menuId:[0-9]+}', ['uses' => 'MenusController@destroy', 'as' => 'menus.destroy', 'middleware' => 'permission:system.menu.destroy']);

                /**
                 * 权限
                 */
                $router->get('systems/permissions', ['uses' => 'PermissionsController@index', 'as' => 'permissions.index', 'middleware' => 'permission:system.permission.list']);
                $router->get('systems/permissions/{permissionId:[0-9]+}', ['uses' => 'PermissionsController@show', 'as' => 'permissions.show', 'middleware' => 'permission:system.permission.show']);
                $router->post('systems/permissions', ['uses' => 'PermissionsController@store', 'as' => 'permissions.store', 'middleware' => 'permission:system.permission.store']);
                $router->patch('systems/permissions/{permissionId:[0-9]+}', ['uses' => 'PermissionsController@update', 'as' => 'permissions.update', 'middleware' => 'permission:system.permission.update']);
                $router->delete('systems/permissions/{permissionId:[0-9]+}', ['uses' => 'PermissionsController@destroy', 'as' => 'permissions.destroy', 'middleware' => 'permission:system.permission.destroy']);

                /**
                 * 角色
                 */
                $router->get('systems/roles', ['uses' => 'RolesController@index', 'as' => 'roles.index', 'middleware' => 'permission:system.role.list']);
                $router->get('systems/roles/{ruleId:[0-9]+}', ['uses' => 'RolesController@show', 'as' => 'roles.show', 'middleware' => 'permission:system.role.show']);
                $router->post('systems/roles', ['uses' => 'RolesController@store', 'as' => 'roles.store', 'middleware' => 'permission:system.role.store']);
                $router->patch('systems/roles/{ruleId:[0-9]+}', ['uses' => 'RolesController@update', 'as' => 'roles.update', 'middleware' => 'permission:system.role.update']);
                $router->delete('systems/roles/{ruleId:[0-9]+}', ['uses' => 'RolesController@destroy', 'as' => 'roles.destroy', 'middleware' => 'permission:system.role.destroy']);

                /**
                 * 管理员
                 */
                $router->get('systems/users', ['uses' => 'UsersController@index', 'as' => 'users.index', 'middleware' => 'permission:system.user.list']);
                $router->get('systems/users/{userId:[0-9]+}', ['uses' => 'UsersController@show', 'as' => 'users.show', 'middleware' => 'permission:system.user.show']);
                $router->post('systems/users', ['uses' => 'UsersController@store', 'as' => 'users.store', 'middleware' => 'permission:system.user.store']);
                $router->patch('systems/users/{userId:[0-9]+}', ['uses' => 'UsersController@update', 'as' => 'users.update', 'middleware' => 'permission:system.user.update']);
                $router->delete('systems/users/{userId:[0-9]+}', ['uses' => 'UsersController@destroy', 'as' => 'users.destroy', 'middleware' => 'permission:system.user.destroy']);

                /**
                 * 数据字典
                 */

                $router->post('systems/dictionaries', ['uses' => 'DictionariesController@store', 'as' => 'dictionaries.store', 'middleware' => 'permission:system.dictionary.store']);
                $router->patch('systems/dictionaries/{dictionaryId:[0-9]+}', ['uses' => 'DictionariesController@update', 'as' => 'dictionaries.update', 'middleware' => 'permission:system.dictionary.update']);
                $router->delete('systems/dictionaries/{dictionaryId:[0-9]+}', ['uses' => 'DictionariesController@destroy', 'as' => 'dictionaries.destroy', 'middleware' => 'permission:system.dictionary.destroy']);

                $router->post('systems/dictionaries/types', ['uses' => 'DictionaryTypesController@store', 'as' => 'dictionaries.types.store', 'middleware' => 'permission:system.dictionaryType.store']);
                $router->patch('systems/dictionaries/types/{typeId:[0-9]+}', ['uses' => 'DictionaryTypesController@update', 'as' => 'dictionaries.types.update', 'middleware' => 'permission:system.dictionaryType.update']);
                $router->delete('systems/dictionaries/types/{typeId:[0-9]+}', ['uses' => 'DictionaryTypesController@destroy', 'as' => 'dictionaries.types.destroy', 'middleware' => 'permission:system.dictionaryType.destroy']);

                /**
                 * 素材
                 */
                $router->get('systems/materials', ['uses' => 'MaterialsController@index', 'as' => 'materials.index', 'middleware' => 'permission:system.material.list']);
                $router->get('systems/materials/{materialId:[0-9]+}', ['uses' => 'MaterialsController@show', 'as' => 'materials.show', 'middleware' => 'permission:system.material.show']);
                $router->post('systems/materials', ['uses' => 'MaterialsController@store', 'as' => 'materials.store', 'middleware' => 'permission:system.material.store']);
                $router->patch('systems/materials/{materialId:[0-9]+}', ['uses' => 'MaterialsController@update', 'as' => 'materials.update', 'middleware' => 'permission:system.material.update']);
                $router->delete('systems/materials/{materialId:[0-9]+}', ['uses' => 'MaterialsController@destroy', 'as' => 'materials.destroy', 'middleware' => 'permission:system.material.destroy']);

                $router->get('systems/materials/groups', ['uses' => 'MaterialGroupsController@index', 'as' => 'materials.groups.index', 'middleware' => 'permission:system.materialGroup.list']);
                $router->get('systems/materials/groups/{groupId:[0-9]+}', ['uses' => 'MaterialGroupsController@show', 'as' => 'materials.groups.show', 'middleware' => 'permission:system.materialGroup.show']);
                $router->post('systems/materials/groups', ['uses' => 'MaterialGroupsController@store', 'as' => 'materials.store', 'materials.groups' => 'permission:system.materialGroup.store']);
                $router->patch('systems/materials/groups/{groupId:[0-9]+}', ['uses' => 'MaterialGroupsController@update', 'as' => 'materials.groups.update', 'middleware' => 'permission:system.materialGroup.update']);
                $router->delete('systems/materials/groups/{groupId:[0-9]+}', ['uses' => 'MaterialGroupsController@destroy', 'as' => 'materials.groups.destroy', 'middleware' => 'permission:system.materialGroup.destroy']);
            });


            $router->group([
                'namespace' => 'Users',
                'as' => 'users'
            ], function ($router) {
                /**
                 * 用户管理
                 */
                $router->get('users', ['uses' => 'UsersController@index', 'as' => 'index', 'middleware' => 'permission:user.list']);
                $router->get('users/{userId:[0-9]+}', ['uses' => 'UsersController@show', 'as' => 'show', 'middleware' => 'permission:user.show']);

                /**
                 * 用户地址管理
                 */
                $router->get('users/addresses', ['uses' => 'UserAddressesController@index', 'as' => 'addresses.index', 'middleware' => 'permission:user.address.list']);
                $router->get('users/addresses/{addressId:[0-9]+}', ['uses' => 'UserAddressesController@show', 'as' => 'addresses.show', 'middleware' => 'permission:user.address.show']);
            });

            /**
             * 商品管理
             */
            $router->group([
                'namespace' => 'Products',
                'as' => 'products'
            ], function ($router) {
                /**
                 * 普通商品
                 */
                $router->get('products', ['uses' => 'ProductsController@index', 'as' => 'index', 'middleware' => 'permission:product.normal.list']);
                $router->get('products/{productId:[0-9]+}', ['uses' => 'ProductsController@show', 'as' => 'show', 'middleware' => 'permission:product.normal.show']);
                $router->post('products', ['uses' => 'ProductsController@store', 'as' => 'store', 'middleware' => 'permission:product.normal.store']);
                $router->patch('products/{productId:[0-9]+}', ['uses' => 'ProductsController@update', 'as' => 'update', 'middleware' => 'permission:product.normal.update']);
                $router->delete('products/{productId:[0-9]+}', ['uses' => 'ProductsController@destroy', 'as' => 'destroy', 'middleware' => 'permission:product.normal.destroy']);

                /**
                 * 众筹商品
                 */
                $router->get('products/crowdfunding', ['uses' => 'CrowdfundingProductsController@index', 'as' => 'crowdfunding.index', 'middleware' => 'permission:product.crowdfunding.list']);
                $router->get('products/crowdfunding/{productId:[0-9]+}', ['uses' => 'CrowdfundingProductsController@show', 'as' => 'crowdfunding.show', 'middleware' => 'permission:product.crowdfunding.show']);
                $router->post('products/crowdfunding', ['uses' => 'CrowdfundingProductsController@store', 'as' => 'crowdfunding.store', 'middleware' => 'permission:product.crowdfunding.store']);
                $router->patch('products/crowdfunding/{productId:[0-9]+}', ['uses' => 'CrowdfundingProductsController@update', 'as' => 'crowdfunding.update', 'middleware' => 'permission:product.crowdfunding.update']);
                $router->delete('products/crowdfunding/{productId:[0-9]+}', ['uses' => 'CrowdfundingProductsController@destroy', 'as' => 'crowdfunding.destroy', 'middleware' => 'permission:product.crowdfunding.destroy']);

                // 商品详情页
                $router->get('products/{productId:[0-9]+}/detail', ['uses' => 'ProductsController@detail', 'as' => 'products.detail']);

                /**
                 * 商品 SKU
                 */
                // 删除单个 SKU
                $router->delete('products/skus/{skuId:[0-9]+}', ['uses' => 'ProductSkusController@skuDestroy', 'as' => 'skus.destroy', 'middleware' => 'permission:product.sku.destroy']);
                // 删除多个 SKU
                $router->post('products/skus/delete', ['uses' => 'ProductSkusController@skusDestroy', 'as' => 'skus.destroyMany', 'middleware' => 'permission:product.sku.destroyMany']);

                /**
                 * 商品类目管理
                 */
                $router->get('products/categories', ['uses' => 'CategoriesController@index', 'as' => 'categories.index', 'middleware' => 'permission:product.category.list']);
                $router->get('products/categories/{categoryId:[0-9]+}', ['uses' => 'CategoriesController@show', 'as' => 'categories.show', 'middleware' => 'permission:product.category.show']);
                $router->post('products/categories', ['uses' => 'CategoriesController@store', 'as' => 'categories.store', 'middleware' => 'permission:product.category.store']);
                $router->patch('products/categories/{categoryId:[0-9]+}', [ 'uses' => 'CategoriesController@update', 'as' => 'categories.update', 'middleware' => 'permission:product.category.update']);
                $router->delete('products/categories/{categoryId:[0-9]+}', ['uses' => 'CategoriesController@destroy', 'as' => 'categories.destroy', 'middleware' => 'permission:product.category.destroy']);
                $router->get('products/categories/tree/', ['uses' => 'CategoriesController@categoriesTree', 'as' => 'categories.tree', 'middleware' => 'permission:product.category.tree']);

                /**
                 * 商品规格模板管理
                 */
                $router->get('products/attributes/templates', ['uses' => 'AttributeTemplatesController@index', 'as' => 'attributesTemplates.index', 'middleware' => 'permission:product.attributeTemplate.list']);
                $router->get('products/attributes/templates/{templateId:[0-9]+}', ['uses' => 'AttributeTemplatesController@show', 'as' => 'attributesTemplates.show', 'middleware' => 'permission:product.attributeTemplate.show']);
                $router->post('products/attributes/templates', ['uses' => 'AttributeTemplatesController@store', 'as' => 'attributesTemplates.store', 'middleware' => 'permission:product.attributeTemplate.store']);
                $router->patch('products/attributes/templates/{templateId:[0-9]+}', [ 'uses' => 'AttributeTemplatesController@update', 'as' => 'attributesTemplates.update', 'middleware' => 'permission:product.attributeTemplate.update']);
                $router->delete('products/attributes/templates/{templateId:[0-9]+}', ['uses' => 'AttributeTemplatesController@destroy', 'as' => 'attributesTemplates.destroy', 'middleware' => 'permission:product.attributeTemplate.destroy']);

                /**
                 * 物流公司管理
                 */
                $router->get('expresses/companies', ['uses' => 'ExpressCompaniesController@index', 'as' => 'expressCompanies.index', 'middleware' => 'permission:express.company.list']);
                $router->get('expresses/companies/{companyId:[0-9]+}', ['uses' => 'ExpressCompaniesController@show', 'as' => 'expressCompanies.show', 'middleware' => 'permission:express.company.show']);
                $router->post('expresses/companies', ['uses' => 'ExpressCompaniesController@store', 'as' => 'expressCompanies.store', 'middleware' => 'permission:express.company.store']);
                $router->patch('expresses/companies/{companyId:[0-9]+}', [ 'uses' => 'ExpressCompaniesController@update', 'as' => 'expressCompanies.update', 'middleware' => 'permission:express.company.update']);
                $router->delete('expresses/companies/{companyId:[0-9]+}', ['uses' => 'ExpressCompaniesController@destroy', 'as' => 'expressCompanies.destroy', 'middleware' => 'permission:express.company.destroy']);

                /**
                 * 运费模板管理
                 */
                $router->get('expresses/fees', ['uses' => 'ExpressFeesController@index', 'as' => 'expressFees.index', 'middleware' => 'permission:express.fee.list']);
                $router->get('expresses/fees/{feeId:[0-9]+}', ['uses' => 'ExpressFeesController@show', 'as' => 'expressFees.show', 'middleware' => 'permission:express.fee.show']);
                $router->post('expresses/fees', ['uses' => 'ExpressFeesController@store', 'as' => 'expressFees.store', 'middleware' => 'permission:express.fee.store']);
                $router->patch('expresses/fees/{feeId:[0-9]+}', [ 'uses' => 'ExpressFeesController@update', 'as' => 'expressFees.update', 'middleware' => 'permission:express.fee.update']);
                $router->delete('expresses/fees/{feeId:[0-9]+}', ['uses' => 'ExpressFeesController@destroy', 'as' => 'expressFees.destroy', 'middleware' => 'permission:express.fee.destroy']);

            });

            /**
             * 订单管理
             */
            $router->group([
                'namespace' => 'Orders',
                'as' => 'orders'
            ], function ($router) {
                // 退款理由
                $router->get('orders/refunds/causes', ['uses' => 'RefundsController@getCauses', 'as' => 'refunds.causes']);

                /**
                 * 订单退款
                 */
                $router->get('orders/items/refunds', ['uses' => 'OrderItemRefundsController@index', 'as' => 'refunds', 'middleware' => 'permission:order.refund.list']);
                $router->get('orders/items/refunds/{refundId:[0-9]+}', ['uses' => 'OrderItemRefundsController@show', 'as' => 'show', 'middleware' => 'permission:order.refund.show']);
                // 确认退款
                $router->post('orders/items/refunds/{refundId:[0-9]+}/refund', ['uses' => 'OrderItemRefundsController@handleRefund', 'as' => 'handle_refund', 'middleware' => 'permission:order.refund.handle_refund']);

                /**
                 * 订单
                 */
                $router->get('orders', ['uses' => 'OrdersController@index', 'as' => 'index', 'middleware' => 'permission:order.list']);
                $router->get('orders/{orderId:[0-9]+}', ['uses' => 'OrdersController@show', 'as' => 'show', 'middleware' => 'permission:order.show']);
                // 某个订单退款
                $router->get('orders/{orderId:[0-9]+}/items/{orderItemId:[0-9]+}/refunds', ['uses' => 'OrdersController@getOrderItemRefunds', 'as' => 'refunds', 'middleware' => 'permission:order.show']);
                // 订单全部发货
                $router->post('orders/{orderId:[0-9]+}/ship', ['uses' => 'OrdersController@ship', 'as' => 'ship', 'middleware' => 'permission:order.ship']);
                // 订单部分发货
                $router->post('orders/{orderId:[0-9]+}/partially_ship', ['uses' => 'OrdersController@partiallyShip', 'as' => 'partially_ship', 'middleware' => 'permission:order.partially_ship']);
            });
        });
    });

    /**
     * 验证
     */
    // 图片验证码
    $router->post('captchas', ['uses' => 'CaptchasController@store', 'as' => 'captchas.store']);

    /**
     * 授权登录
     */
    // 账号密码登录
    $router->post('authorizations', ['uses' => 'AuthorizationsController@store', 'as' => 'authorizations.store']);
    // 刷新授权 token
    $router->put('authorizations/current', ['uses' => 'AuthorizationsController@update', 'as' => 'authorizations.update']);
    // 删除授权
    $router->delete('authorizations/current', ['uses' => 'AuthorizationsController@destroy', 'as' => 'authorizations.destroy']);

    /**
     * 订单通知
     */
    // 订单微信支付通知
    $router->post('payment_notifications/wechat/order/payment', ['uses' => 'PaymentNotifiesController@orderWechatPaid', 'as' => 'payment_notifications.wechat.order.payment']);
    // 订单微信退款通知
    $router->post('payment_notifications/wechat/order/refund', ['uses' => 'PaymentNotifiesController@orderWechatRefunded', 'as' => 'payment_notifications.wechat.order.refund']);
});
