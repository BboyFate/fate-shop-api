<?php

$router->group([
    'prefix' => 'api/v1',
    'namespace' => 'Api\V1'
], function () use ($router) {
    // 图片验证码
    $router->post('captchas', [
        'uses' => 'CaptchasController@store',
        'as' => 'api.v1.captchas.store'
    ]);

    // 短信验证码
    $router->post('verificationCodes', [
        'uses' => 'VerificationCodesController@store',
        'as' => 'api.v1.verificationCodes.store'
    ]);

    // 账号密码登录
    $router->post('authorizations', [
        'uses' => 'AuthorizationsController@store',
        'as' => 'api.v1.authorizations.store'
    ]);
    // 第三方登录
    $router->post('socials/{social_type}/authorizations', [
        'uses' => 'AuthorizationsController@socialStore',
        'as' => 'api.v1.socials.authorizations.store'
    ]);
    // 手机验证码登录
    $router->post('authorizations/smsCode', [
        'uses' => 'AuthorizationsController@smsCodeStore',
        'as' => 'api.v1.authorizations.smsCodeStore'
    ]);
    // 刷新授权 token
    $router->put('authorizations/current', [
        'uses' => 'AuthorizationsController@update',
        'as' => 'api.v1.authorizations.update'
    ]);
    // 删除授权
    $router->delete('authorizations/current', [
        'uses' => 'AuthorizationsController@destroy',
        'as' => 'api.v1.authorizations.destroy'
    ]);


    /**
     * 商品
     */
    $router->get('products', [
        'uses' => 'ProductsController@index',
        'as' => 'api.v1.products.index'
    ]);
    $router->get('products/{id:[0-9]+}', [
        'uses' => 'ProductsController@show',
        'as' => 'api.v1.products.show'
    ]);

    /**
     * 需要登录才能访问的 API
     */
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        /**
         * 商品
         */
        $router->get('products/favorites', [
            'uses' => 'ProductsController@favorites',
            'as' => 'api.v1.products.favorites'
        ]);
        $router->post('products/{product}/favorite', [
            'uses' => 'ProductsController@favor',
            'as' => 'api.v1.products.favor'
        ]);
        $router->delete('products/{product}/favorite', [
            'uses' => 'ProductsController@disfavor',
            'as' => 'api.v1.products.disfavor'
        ]);
    });
});
