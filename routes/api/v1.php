<?php

$router->group([
    'prefix' => 'api/v1',
    'namespace' => 'Api\V1'
], function () use ($router) {
    // 图片验证码
    $router->post('captchas', [
        'uses' => 'CaptchasController@store',
        'as' => 'captchas.store'
    ]);

    // 短信验证码
    $router->post('verificationCodes', [
        'uses' => 'VerificationCodesController@store',
        'as' => 'verificationCodes.store'
    ]);

    // 账号密码登录
    $router->post('authorizations', [
        'uses' => 'AuthorizationsController@store',
        'as' => 'authorizations.store'
    ]);
    // 第三方登录
    $router->post('socials/{social_type}/authorizations', [
        'uses' => 'AuthorizationsController@socialStore',
        'as' => 'socials.authorizations.store'
    ]);
    // 手机验证码登录
    $router->post('authorizations/smsCode', [
        'uses' => 'AuthorizationsController@smsCodeStore',
        'as' => 'authorizations.smsCodeStore'
    ]);
    // 刷新授权 token
    $router->put('authorizations/current', [
        'uses' => 'AuthorizationsController@update',
        'as' => 'authorizations.update'
    ]);
    // 删除授权
    $router->delete('authorizations/current', [
        'uses' => 'AuthorizationsController@destroy',
        'as' => 'authorizations.destroy'
    ]);
});
