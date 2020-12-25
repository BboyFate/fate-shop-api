<?php

$router->group([
    'prefix' => 'api/v1',
    'namespace' => 'V1'
], function () use ($router) {

    // 秒杀商品
    $router->post('seckill_orders', ['middleware' => 'random_drop:5', 'uses' => 'OrdersController@seckill', 'as' => 'api.v1.seckill_orders.store']);

    /**
     * 验证
     */
    // 图片验证码
    $router->post('captchas', ['uses' => 'CaptchasController@store', 'as' => 'api.v1.captchas.store']);
    // 手机获取短信验证码
    $router->post('verification_codes/sms', ['uses' => 'VerificationCodesController@smsStore', 'as' => 'api.v1.verification_codes.sms_store']);

    /**
     * 授权登录
     */
    // 账号密码登录
    $router->post('authorizations', ['uses' => 'AuthorizationsController@store', 'as' => 'api.v1.authorizations.store']);
    // 第三方登录
    $router->post('socials/{type}/authorizations', ['uses' => 'AuthorizationsController@socialStore', 'as' => 'api.v1.socials.authorizations.store']);
    // 手机验证码登录
    $router->post('authorizations/sms', ['uses' => 'AuthorizationsController@storeBySms', 'as' => 'api.v1.authorizations.sms_store']);

    /**
     * 授权
     */
    // 刷新授权 token
    $router->put('authorizations/current', ['uses' => 'AuthorizationsController@update', 'as' => 'api.v1.authorizations.update']);
    // 删除授权
    $router->delete('authorizations/current', ['uses' => 'AuthorizationsController@destroy', 'as' => 'api.v1.authorizations.destroy']);

    /**
     * 微信小程序
     */
    // 账号密码登录
    $router->post('weapp/authorizations/account', ['uses' => 'AuthorizationsController@weappStoreByAccount', 'as' => 'api.v1.weapp.authorizations.account_store']);
    // 手机验证码登录
    $router->post('weapp/authorizations/sms', ['uses' => 'AuthorizationsController@weappStoreBySms', 'as' => 'api.v1.weapp.authorizations.sms_store']);
    // 手机注册
    $router->post('registrations/weapp/phone', ['uses' => 'UsersController@storeByWeappPhone', 'as' => 'api.v1.weapp.users.store_by_phone']);
    // 小程序首页
    $router->get('weapp/index', ['uses' => 'IndexController@weappIndex', 'as' => 'api.v1.weapp.index']);


    /**
     * 商品
     */
    $router->get('products', ['uses' => 'ProductsController@index', 'as' => 'api.v1.products.index']);
    $router->get('products/{id:[0-9]+}', ['uses' => 'ProductsController@show', 'as' => 'api.v1.products.show']);
    // 热门商品
    $router->get('products/hot', ['uses' => 'ProductsController@getHotProduct', 'as' => 'api.v1.products.hot']);
    // 商品评论列表
    $router->get('products/{id:[0-9]+}/reviews', ['uses' => 'ProductsController@reviews', 'as' => 'api.v1.products.reviews']);
    // 商品分类
    $router->get('product_categories', ['uses' => 'ProductsController@getCategories', 'as' => 'api.v1.product_categories.index']);

    /**
     * 支付
     */
    $router->get('payment/wechat/notify', ['uses' => 'PaymentController@wechatNotify', 'as' => 'api.v1.payment.wechat.notify']);
    $router->post('payment/wechat/refund_notify', ['uses' => 'PaymentController@wechatRefundNotify', 'as' => 'api.v1.payment.wechat.refund_notify']);

    /**
     * 需要登录才能访问的 API
     */
    $router->group([
        'middleware' => 'auth_refresh'
    ], function () use ($router) {

        /**
         * 当前用户
         */
        // 当前登录用户信息
        $router->get('user', ['uses' => 'UsersController@me', 'as' => 'api.v1.user.show']);

        /**
         * 商品收藏
         */
        // 收藏列表
        $router->get('products/favorites', ['uses' => 'ProductsController@favorites', 'as' => 'api.v1.products.favorites']);
        // 收藏的某个商品
        $router->get('products/{productId:[0-9]+}/favorite', ['uses' => 'ProductsController@favorite', 'as' => 'api.v1.products.favorite']);
        // 添加收藏
        $router->post('products/{productId:[0-9]+}/favorite', ['uses' => 'ProductsController@favor', 'as' => 'api.v1.products.favor']);
        // 删除收藏
        $router->delete('products/{productId:[0-9]+}/favorite', ['uses' => 'ProductsController@disfavor', 'as' => 'api.v1.products.disfavor']);

        /**
         * 用户收货地址
         */
        $router->get('user_addresses', ['uses' => 'UserAddressesController@index', 'as' => 'api.v1.user_addresses.index']);
        $router->post('user_addresses', ['uses' => 'UserAddressesController@store', 'as' => 'api.v1.user_addresses.store']);
        $router->put('user_addresses/{user_address:[0-9]+}', ['uses' => 'UserAddressesController@update', 'as' => 'api.v1.user_addresses.update']);
        $router->delete('user_addresses/{user_address:[0-9]+}', ['uses' => 'UserAddressesController@destroy', 'as' => 'api.v1.user_addresses.destroy']);

        /**
         * 购物车
         */
        $router->get('user/cart', ['uses' => 'UserCartController@index', 'as' => 'api.v1.user.cart.index']);
        $router->post('user/cart', ['uses' => 'UserCartController@store', 'as' => 'api.v1.user.cart.store']);
        $router->delete('user/cart/{sku:[0-9]+}', ['uses' => 'UserCartController@destroy', 'as' => 'api.v1.user.cart.destroy']);

        /**
         * 订单
         */
        $router->get('orders', ['uses' => 'OrdersController@index', 'as' => 'api.v1.orders.index']);
        $router->get('orders/{order:[0-9]+}', ['uses' => 'OrdersController@show', 'as' => 'api.v1.orders.show']);
        $router->post('orders', ['uses' => 'OrdersController@store', 'as' => 'api.v1.orders.store']);
        $router->post('orders/{order:[0-9]+}/received', ['uses' => 'OrdersController@received', 'as' => 'api.v1.orders.received']);
        $router->post('orders/{order:[0-9]+}/review', ['uses' => 'OrdersController@sendReview', 'as' => 'api.v1.orders.review.store']);
        $router->post('orders/{order:[0-9]+}/apply_refund', ['uses' => 'OrdersController@applyRefund', 'as' => 'api.v1.orders.apply_refund']);
        $router->post('crowdfunding_orders', ['uses' => 'OrdersController@crowdfunding', 'as' => 'api.v1.crowdfunding_orders.store']);

        /**
         * 支付
         */
        $router->post('payment/{order:[0-9]+}/weapp', ['uses' => 'PaymentController@payByWechatMiniapp', 'as' => 'api.v1.payment.weapp']);
    });
});
