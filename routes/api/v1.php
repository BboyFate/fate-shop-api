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
        $router->get('user/products/favorites', ['uses' => 'ProductsController@favorites', 'as' => 'api.v1.user.products.favorites.index']);
        // 收藏的某个商品
        $router->get('user/products/{productId:[0-9]+}/favorites', ['uses' => 'ProductsController@favoriteShow', 'as' => 'api.v1.user.products.favorites.show']);
        // 添加收藏
        $router->post('user/products/{productId:[0-9]+}/favorites', ['uses' => 'ProductsController@favoriteStore', 'as' => 'api.v1.user.products.favorites.store']);
        // 删除收藏
        $router->delete('user/products/{productId:[0-9]+}/favorites', ['uses' => 'ProductsController@favoritesDestroy', 'as' => 'api.v1.user.products.favorites.destroy']);
        // 删除多项收藏
        $router->post('user/products/favorites', ['uses' => 'ProductsController@favoriteDestroys', 'as' => 'api.v1.user.products.favorites.destroys']);

        /**
         * 用户收货地址
         */
        $router->get('user/addresses', ['uses' => 'UserAddressesController@index', 'as' => 'api.v1.user.addresses.index']);
        $router->get('user/addresses/default', ['uses' => 'UserAddressesController@defaultAddress', 'as' => 'api.v1.user.addresses.default']);
        $router->post('user/addresses', ['uses' => 'UserAddressesController@store', 'as' => 'api.v1.user.addresses.store']);
        $router->put('user/addresses/{addressId:[0-9]+}', ['uses' => 'UserAddressesController@update', 'as' => 'api.v1.user.addresses.update']);
        $router->delete('user/addresses/{addressId:[0-9]+}', ['uses' => 'UserAddressesController@destroy', 'as' => 'api.v1.user.addresses.destroy']);

        /**
         * 购物车
         */
        $router->get('user/cart', ['uses' => 'UserCartController@index', 'as' => 'api.v1.user.cart.index']);
        $router->post('user/cart', ['uses' => 'UserCartController@store', 'as' => 'api.v1.user.cart.store']);
        $router->delete('user/cart/{sku:[0-9]+}', ['uses' => 'UserCartController@destroy', 'as' => 'api.v1.user.cart.destroy']);

        /**
         * 订单
         */
        // 订单列表
        $router->get('orders', ['uses' => 'OrdersController@index', 'as' => 'api.v1.orders.index']);
        // 创建订单
        $router->post('orders', ['uses' => 'OrdersController@store', 'as' => 'api.v1.orders.store']);
        // 订单详情
        $router->get('orders/{orderId:[0-9]+}', ['uses' => 'OrdersController@showOrder', 'as' => 'api.v1.orders.show']);
        // 取消订单
        $router->post('orders/{orderId:[0-9]+}/close', ['uses' => 'OrdersController@closeOrder', 'as' => 'api.v1.orders.close']);
        // 订单 微信小程序支付
        $router->post('orders/{orderId:[0-9]+}/payment/weapp', ['uses' => 'OrdersController@payByWechatMiniapp', 'as' => 'api.v1.orders.payment.weapp']);
        // 生成订单
        $router->post('orders/generate', ['uses' => 'OrdersController@generateOrder', 'as' => 'api.v1.orders.generate']);
        // 计算订单金额
        $router->post('orders/calculate', ['uses' => 'OrdersController@calcOrder', 'as' => 'api.v1.orders.calc']);

        // 子订单 售后理由列表
        $router->get('orders/items/refunds/causes', ['uses' => 'OrderRefundsController@causes', 'as' => 'api.v1.orders.items.refunds.causes']);
        // 子订单 售后列表
        $router->get('orders/items/refunds', ['uses' => 'OrderRefundsController@refunds', 'as' => 'api.v1.orders.items.refunds.index']);
        // 子订单 售后详情
        $router->get('orders/items/refunds/{refundId:[0-9]+}', ['uses' => 'OrderRefundsController@refundShow', 'as' => 'api.v1.orders.items.refunds.show']);
        // 子订单 提交售后
        $router->post('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}/refunds', ['uses' => 'OrderRefundsController@refundStore', 'as' => 'api.v1.orders.items.refunds.store']);
        // 子订单 删除售后单
        $router->delete('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}/refunds', ['uses' => 'OrderRefundsController@refundDestroy', 'as' => 'api.v1.orders.items.refunds.destroy']);

        // 子订单 详情
        $router->get('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}', ['uses' => 'OrdersController@showOrderItem', 'as' => 'api.v1.orders.items.show']);
        // 子订单 删除
        $router->delete('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}', ['uses' => 'OrdersController@destroyItem', 'as' => 'api.v1.orders.items.destroy']);
        // 子订单 评论
        $router->post('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}/review', ['uses' => 'OrdersController@itemReviewStore', 'as' => 'api.v1.orders.items.review.store']);
        // 子订单 确认收货
        $router->post('orders/{orderId:[0-9]+}/items/{itemId:[0-9]+}/received', ['uses' => 'OrdersController@receivedItem', 'as' => 'api.v1.orders.items.received']);

        /**
         * 图片
         */
        $router->post('user/images', ['uses' => 'UserImagesController@store', 'as' => 'api.v1.user.images.store']);
        $router->delete('user/images/{imageId:[0-9]+}', [ 'uses' => 'UserImagesController@destroy', 'as' => 'api.v1.user.images.destroy']);
    });
});
