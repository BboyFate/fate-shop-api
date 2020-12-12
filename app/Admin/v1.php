<?php

$router->group([
    'prefix' => 'api/v1/admin',
    'namespace' => 'V1',
    'middleware' => [
        'accept_header',
        'admin_guard',
    ],
], function () use ($router) {
    /**
     * 验证
     */
    // 图片验证码
    $router->post('captchas', ['uses' => 'CaptchasController@store', 'as' => 'api.v1.admin.captchas.store']);

    /**
     * 授权登录
     */
    // 账号密码登录
    $router->post('authorizations', ['uses' => 'AuthorizationsController@store', 'as' => 'api.v1.admin.authorizations.store']);
    // 刷新授权 token
    $router->put('authorizations/current', ['uses' => 'AuthorizationsController@update', 'as' => 'api.v1.admin.authorizations.update']);
    // 删除授权
    $router->delete('authorizations/current', ['uses' => 'AuthorizationsController@destroy', 'as' => 'api.v1.admin.authorizations.destroy']);

    /**
     * 需要登录才能访问的 API
     */
    $router->group(['middleware' => [
        'auth_refresh',
        'admin_check_permissions'
    ]], function () use ($router) {
        /**
         * 图片
         */
        $router->get('images', ['uses' => 'SystemImagesController@index', 'as' => 'api.v1.admin.images.index']);
        $router->post('images', ['uses' => 'SystemImagesController@store', 'as' => 'api.v1.admin.images.store']);
        $router->delete('images/{image:[0-9]+}', [ 'uses' => 'SystemImagesController@destroy', 'as' => 'api.v1.admin.images.store']);
        $router->post('images/delete', ['uses' => 'SystemImagesController@imagesDestroy', 'as' => 'api.v1.admin.images.images_destroy']);

        $router->group(['namespace' => 'Auth'], function ($router) {
            /**
             * 当前登录的管理员
             */
            $router->get('user', ['uses' => 'UsersController@me', 'as' => 'api.v1.admin.user.me']);
            $router->patch('user', ['uses' => 'UsersController@meUpdate', 'as' => 'api.v1.admin.user.me_update']);

            /**
             * 管理员
             */
            $router->get('auth/users', ['uses' => 'UsersController@index', 'as' => 'api.v1.admin.auth.users.index']);
            $router->get('auth/users/{id:[0-9]+}', ['uses' => 'UsersController@show', 'as' => 'api.v1.admin.auth.users.show']);
            $router->post('auth/users', ['uses' => 'UsersController@store', 'as' => 'api.v1.admin.auth.users.store']);
            $router->patch('auth/users/{id:[0-9]+}', ['uses' => 'UsersController@update', 'as' => 'api.v1.admin.auth.users.update']);
            $router->delete('auth/users/{user:[0-9]+}', ['uses' => 'UsersController@destroy', 'as' => 'api.v1.admin.auth.users.destroy']);

            /**
             * 权限
             */
            $router->get('auth/permissions', ['uses' => 'PermissionsController@index', 'as' => 'api.v1.admin.auth.permissions.index']);
            $router->get('auth/permissions/{id:[0-9]+}', ['uses' => 'PermissionsController@show', 'as' => 'api.v1.admin.auth.permissions.show']);
            $router->post('auth/permissions', ['uses' => 'PermissionsController@store', 'as' => 'api.v1.admin.auth.permissions.store']);
            $router->patch('auth/permissions/{id:[0-9]+}', ['uses' => 'PermissionsController@update', 'as' => 'api.v1.admin.auth.permissions.update']);
            $router->delete('auth/permissions/{role:[0-9]+}', ['uses' => 'PermissionsController@destroy', 'as' => 'api.v1.admin.auth.permissions.destroy']);

            /**
             * 角色
             */
            $router->get('auth/roles', ['uses' => 'RolesController@index', 'as' => 'api.v1.admin.auth.roles.index']);
            $router->get('auth/roles/{id:[0-9]+}', ['uses' => 'RolesController@show', 'as' => 'api.v1.admin.auth.roles.show']);
            $router->post('auth/roles', ['uses' => 'RolesController@store', 'as' => 'api.v1.admin.auth.roles.store']);
            $router->patch('auth/roles/{id:[0-9]+}', ['uses' => 'RolesController@update', 'as' => 'api.v1.admin.auth.roles.update']);
            $router->delete('auth/roles/{role:[0-9]+}', ['uses' => 'RolesController@destroy', 'as' => 'api.v1.admin.auth.roles.destroy']);

            /**
             * vue 菜单
             */
            $router->get('vue_menus', ['uses' => 'VueMenusController@index', 'as' => 'api.v1.admin.auth.vue_menus.index']);
            $router->get('vue_menus/{id:[0-9]+}', ['uses' => 'VueMenusController@show', 'as' => 'api.v1.admin.auth.vue_menus.show']);
            $router->post('vue_menus', ['uses' => 'VueMenusController@store', 'as' => 'api.v1.admin.auth.vue_menus.store']);
            $router->patch('vue_menus/{id:[0-9]+}', ['uses' => 'VueMenusController@update', 'as' => 'api.v1.admin.auth.vue_menus.update']);
            $router->delete('vue_menus/{role:[0-9]+}', ['uses' => 'VueMenusController@destroy', 'as' => 'api.v1.admin.auth.vue_menus.destroy']);
            $router->get('vue_menus/role_menus', ['uses' => 'VueMenusController@roleMenus', 'as' => 'api.v1.admin.auth.vue_menus.role_menus']);
        });

        /**
         * 用户管理
         */
        $router->group([], function ($router) {
            $router->get('users', ['uses' => 'UsersController@index', 'as' => 'api.v1.admin.users.index']);
            $router->get('users/{id:[0-9]+}', ['uses' => 'UsersController@show', 'as' => 'api.v1.admin.users.show']);
        });

        /**
         * 用户地址管理
         */
        $router->group([], function ($router) {
            $router->get('user_addresses', ['uses' => 'UserAddressesController@index', 'as' => 'api.v1.admin.user_addresses.index']);
            $router->get('user_addresses/{id:[0-9]+}', ['uses' => 'UserAddressesController@show', 'as' => 'api.v1.admin.user_addresses.show']);
        });

        /**
         * 商品管理
         */
        $router->group([], function ($router) {
            /**
             * 普通商品
             */
            $router->get('products', ['uses' => 'ProductsController@index', 'as' => 'api.v1.admin.products.index']);
            $router->get('products/{id:[0-9]+}', ['uses' => 'ProductsController@show', 'as' => 'api.v1.admin.products.show']);
            $router->post('products', ['uses' => 'ProductsController@store', 'as' => 'api.v1.admin.products.store']);
            $router->patch('products/{id:[0-9]+}', ['uses' => 'ProductsController@update', 'as' => 'api.v1.admin.products.update']);
            $router->delete('products/{id:[0-9]+}', ['uses' => 'ProductsController@destroy', 'as' => 'api.v1.admin.products.destroy']);

            /**
             * 众筹商品
             */
            $router->get('crowdfunding_products', ['uses' => 'CrowdfundingProductsController@index', 'as' => 'api.v1.admin.crowdfunding_products.index']);
            $router->get('crowdfunding_products/{id:[0-9]+}', ['uses' => 'CrowdfundingProductsController@show', 'as' => 'api.v1.admin.crowdfunding_products.show']);
            $router->post('crowdfunding_products', ['uses' => 'CrowdfundingProductsController@store', 'as' => 'api.v1.admin.crowdfunding_products.store']);
            $router->patch('crowdfunding_products/{id:[0-9]+}', ['uses' => 'CrowdfundingProductsController@update', 'as' => 'api.v1.admin.crowdfunding_products.update']);
            $router->delete('crowdfunding_products/{id:[0-9]+}', ['uses' => 'CrowdfundingProductsController@destroy', 'as' => 'api.v1.admin.crowdfunding_products.destroy']);

            $router->get('products/{id:[0-9]+}/detail', ['uses' => 'ProductsController@detail', 'as' => 'api.v1.admin.products.detail']);

            /**
             * 商品 SKU
             */
            // 删除单个 SKU
            $router->delete('product_skus/{sku:[0-9]+}', ['uses' => 'ProductSkusController@skuDestroy', 'as' => 'api.v1.admin.product_skus.destroy']);
            // 删除多个 SKU
            $router->post('product_skus/delete', ['uses' => 'ProductSkusController@skusDestroy', 'as' => 'api.v1.admin.product_skus.skus_destroy']);
        });

        /**
         * 商品类目管理
         */
        $router->group([], function ($router) {
            $router->get('product_categories', ['uses' => 'ProductCategoriesController@index', 'as' => 'api.v1.admin.product_categories.index']);
            $router->get('product_categories_tree/', ['uses' => 'ProductCategoriesController@categoriesTree', 'as' => 'api.v1.admin.product_categories_tree.index']);
            $router->get('product_categories/{id:[0-9]+}', ['uses' => 'ProductCategoriesController@show', 'as' => 'api.v1.admin.product_categories.show']);
            $router->post('product_categories', ['uses' => 'ProductCategoriesController@store', 'as' => 'api.v1.admin.product_categories.store']);
            $router->patch('product_categories/{id:[0-9]+}', [ 'uses' => 'ProductCategoriesController@update', 'as' => 'api.v1.admin.product_categories.update']);
            $router->delete('product_categories/{id:[0-9]+}', ['uses' => 'ProductCategoriesController@destroy', 'as' => 'api.v1.admin.product_categories.destroy']);
        });

        /**
         * 商品规格模板管理
         */
        $router->group([], function ($router) {
            $router->get('product_attribute_templates', ['uses' => 'ProductAttributeTemplatesController@index', 'as' => 'api.v1.admin.product_attribute_templates.index']);
            $router->get('product_attribute_templates/{id:[0-9]+}', ['uses' => 'ProductAttributeTemplatesController@show', 'as' => 'api.v1.admin.product_attribute_templates.show']);
            $router->post('product_attribute_templates', ['uses' => 'ProductAttributeTemplatesController@store', 'as' => 'api.v1.admin.product_attribute_templates.store']);
            $router->patch('product_attribute_templates/{id:[0-9]+}', [ 'uses' => 'ProductAttributeTemplatesController@update', 'as' => 'api.v1.admin.product_attribute_templates.update']);
            $router->delete('product_attribute_templates/{id:[0-9]+}', ['uses' => 'ProductAttributeTemplatesController@destroy', 'as' => 'api.v1.admin.product_attribute_templates.destroy']);
        });

        /**
         * 订单管理
         */
        $router->group([], function ($router) {
            $router->get('orders', ['uses' => 'OrdersController@index', 'as' => 'api.v1.admin.orders.index']);
            $router->get('orders/{order:[0-9]+}', ['uses' => 'OrdersController@show', 'as' => 'api.v1.admin.orders.show']);
            $router->patch('orders/{order:[0-9]+}/ship', ['uses' => 'OrdersController@ship', 'as' => 'api.v1.admin.orders.ship']);
            $router->patch('orders/{order:[0-9]+}/refund', ['uses' => 'OrdersController@refund', 'as' => 'api.v1.admin.orders.refund']);
        });
    });
});
