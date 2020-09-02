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

        $router->group([], function ($router) {
            /**
             * 管理员
             */
            $router->get('users', [
                'uses' => 'UsersController@index',
                'as' => 'api.v1.admin.users.index'
            ]);
            $router->get('users/{id:[0-9]+}', [
                'uses' => 'UsersController@show',
                'as' => 'api.v1.admin.users.show'
            ]);
            $router->post('users', [
                'uses' => 'UsersController@store',
                'as' => 'api.v1.admin.users.store'
            ]);
            $router->patch('users/{id:[0-9]+}', [
                'uses' => 'UsersController@update',
                'as' => 'api.v1.admin.users.update'
            ]);
            $router->delete('users/{user}', [
                'uses' => 'UsersController@destroy',
                'as' => 'api.v1.admin.users.destroy'
            ]);

            /**
             * 权限
             */
            $router->get('permissions', [
                'uses' => 'PermissionsController@index',
                'as' => 'api.v1.admin.permissions.index'
            ]);
            $router->get('permissions/{id:[0-9]+}', [
                'uses' => 'PermissionsController@show',
                'as' => 'api.v1.admin.permissions.show'
            ]);
            $router->post('permissions', [
                'uses' => 'PermissionsController@store',
                'as' => 'api.v1.admin.permissions.store'
            ]);
            $router->patch('permissions/{id:[0-9]+}', [
                'uses' => 'PermissionsController@update',
                'as' => 'api.v1.admin.permissions.update'
            ]);
            $router->delete('permissions/{role}', [
                'uses' => 'PermissionsController@destroy',
                'as' => 'api.v1.admin.permissions.destroy'
            ]);

            /**
             * 角色
             */
            $router->get('roles', [
                'uses' => 'RolesController@index',
                'as' => 'api.v1.admin.roles.index'
            ]);
            $router->get('roles/{id:[0-9]+}', [
                'uses' => 'RolesController@show',
                'as' => 'api.v1.admin.roles.show'
            ]);
            $router->post('roles', [
                'uses' => 'RolesController@store',
                'as' => 'api.v1.admin.roles.store'
            ]);
            $router->patch('roles/{id:[0-9]+}', [
                'uses' => 'RolesController@update',
                'as' => 'api.v1.admin.roles.update'
            ]);
            $router->delete('roles/{role}', [
                'uses' => 'RolesController@destroy',
                'as' => 'api.v1.admin.roles.destroy'
            ]);
        });

        
    });
});
