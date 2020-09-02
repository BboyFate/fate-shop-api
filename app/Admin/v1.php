<?php

$router->group([
    'prefix' => 'api/v1/admin',
    'namespace' => 'V1',
    'middleware' => ['admin.guard', 'accept_header'],
], function () use ($router) {
    /**
     * 验证
     */
    // 图片验证码
    $router->post('captchas', [
        'uses' => 'CaptchasController@store',
        'as' => 'api.v1.captchas.store'
    ]);

    /**
     * 授权登录
     */
    // 账号密码登录
    $router->post('authorizations', [
        'uses' => 'AuthorizationsController@store',
        'as' => 'api.v1.admin.authorizations.store'
    ]);
    // 刷新授权 token
    $router->put('authorizations/current', [
        'uses' => 'AuthorizationsController@update',
        'as' => 'api.v1.admin.authorizations.update'
    ]);
    // 删除授权
    $router->delete('authorizations/current', [
        'uses' => 'AuthorizationsController@destroy',
        'as' => 'api.v1.admin.authorizations.destroy'
    ]);

    /**
     * 需要登录才能访问的 API
     */
    $router->group(['middleware' => 'admin.refresh'], function () use ($router) {
        // 上传图片
        $router->post('images', [
            'uses' => 'ImagesController@store',
            'as' => 'api.v1.admin.images.store'
        ]);

        $router->group(['namespace' => 'Auth'], function ($router) {
            /**
             * 管理员
             */
            $router->get('auth/users', [
                'uses' => 'UsersController@index',
                'as' => 'api.v1.admin.auth.users.index'
            ]);
            $router->get('auth/users/{id:[0-9]+}', [
                'uses' => 'UsersController@show',
                'as' => 'api.v1.admin.auth.users.show'
            ]);
            $router->post('auth/users', [
                'uses' => 'UsersController@store',
                'as' => 'api.v1.admin.auth.users.store'
            ]);
            $router->patch('auth/users/{id:[0-9]+}', [
                'uses' => 'UsersController@update',
                'as' => 'api.v1.admin.auth.users.update'
            ]);
            $router->delete('auth/users/{user}', [
                'uses' => 'UsersController@destroy',
                'as' => 'api.v1.admin.auth.users.destroy'
            ]);

            /**
             * 权限
             */
            $router->get('auth/permissions', [
                'uses' => 'PermissionsController@index',
                'as' => 'api.v1.admin.auth.permissions.index'
            ]);
            $router->get('auth/permissions/{id:[0-9]+}', [
                'uses' => 'PermissionsController@show',
                'as' => 'api.v1.admin.auth.permissions.show'
            ]);
            $router->post('auth/permissions', [
                'uses' => 'PermissionsController@store',
                'as' => 'api.v1.admin.auth.permissions.store'
            ]);
            $router->patch('auth/permissions/{id:[0-9]+}', [
                'uses' => 'PermissionsController@update',
                'as' => 'api.v1.admin.auth.permissions.update'
            ]);
            $router->delete('auth/permissions/{role}', [
                'uses' => 'PermissionsController@destroy',
                'as' => 'api.v1.admin.auth.permissions.destroy'
            ]);

            /**
             * 角色
             */
            $router->get('auth/roles', [
                'uses' => 'RolesController@index',
                'as' => 'api.v1.admin.auth.roles.index'
            ]);
            $router->get('auth/roles/{id:[0-9]+}', [
                'uses' => 'RolesController@show',
                'as' => 'api.v1.admin.auth.roles.show'
            ]);
            $router->post('auth/roles', [
                'uses' => 'RolesController@store',
                'as' => 'api.v1.admin.auth.roles.store'
            ]);
            $router->patch('auth/roles/{id:[0-9]+}', [
                'uses' => 'RolesController@update',
                'as' => 'api.v1.admin.auth.roles.update'
            ]);
            $router->delete('auth/roles/{role}', [
                'uses' => 'RolesController@destroy',
                'as' => 'api.v1.admin.auth.roles.destroy'
            ]);
        });

    });
});
