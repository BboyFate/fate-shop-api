<?php

$router->group([
    'prefix' => 'api/v1',
    'namespace' => 'Api\V1'
], function () use ($router) {

    // 秒杀商品
    $router->post('seckill_orders', [
        'middleware' => 'random_drop:5',
        'uses' => 'OrdersController@seckill',
        'as' => 'api.v1.seckill_orders.store'
    ]);

    /**
     * 验证
     */
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

    /**
     * 授权登录
     */
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
     * 支付
     */
    $router->get('payment/wechat/notify', [
        'uses' => 'PaymentController@wechatNotify',
        'as' => 'api.v1.payment.wechat.notify'
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

        /**
         * 用户收货地址
         */
        $router->get('user_addresses', [
            'uses' => 'UserAddressesController@index',
            'as' => 'api.v1.user_addresses.index'
        ]);
        $router->post('user_addresses', [
            'uses' => 'UserAddressesController@store',
            'as' => 'api.v1.user_addresses.store'
        ]);
        $router->put('user_addresses/{user_address}', [
            'uses' => 'UserAddressesController@update',
            'as' => 'api.v1.user_addresses.update'
        ]);
        $router->delete('user_addresses/{user_address}', [
            'uses' => 'UserAddressesController@destroy',
            'as' => 'api.v1.user_addresses.destroy'
        ]);

        /**
         * 购物车
         */
        $router->get('cart', [
            'uses' => 'CartController@index',
            'as' => 'api.v1.cart.index'
        ]);
        $router->post('cart', [
            'uses' => 'CartController@store',
            'as' => 'api.v1.cart.store'
        ]);
        $router->delete('cart/{sku}', [
            'uses' => 'CartController@destroy',
            'as' => 'api.v1.cart.destroy'
        ]);

        /**
         * 订单
         */
        $router->get('orders', [
            'uses' => 'OrdersController@index',
            'as' => 'api.v1.orders.index'
        ]);
        $router->get('orders/{order}', [
            'uses' => 'OrdersController@show',
            'as' => 'api.v1.orders.show'
        ]);
        $router->post('orders', [
            'uses' => 'OrdersController@store',
            'as' => 'api.v1.orders.store'
        ]);
        $router->post('orders/{order}/received', [
            'uses' => 'OrdersController@received',
            'as' => 'api.v1.orders.received'
        ]);
        $router->post('orders/{order}/review', [
            'uses' => 'OrdersController@sendReview',
            'as' => 'api.v1.orders.review.store'
        ]);
        $router->post('orders/{order}/apply_refund', [
            'uses' => 'OrdersController@applyRefund',
            'as' => 'api.v1.orders.apply_refund'
        ]);
        $router->post('crowdfunding_orders', [
            'uses' => 'OrdersController@crowdfunding',
            'as' => 'api.v1.crowdfunding_orders.store'
        ]);

        /**
         * 支付
         */
        $router->post('payment/{order}/wechat_mini', [
            'uses' => 'PaymentController@payByWechatMiniapp',
            'as' => 'api.v1.payment.wechatMini'
        ]);
    });
});
