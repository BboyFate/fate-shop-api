<?php

namespace App\Http\Controllers\V1;

use App\Models\UserSocial;
use Illuminate\Http\Request;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use App\Models\User;
use App\Http\Resources\UserResource;

class UsersController extends Controller
{
    /**
     * 当前登录用户信息
     *
     * @param Request $request
     * @return UserResource
     */
    public function me(Request $request)
    {
        return (new UserResource($request->user()))->showSensitiveFields();
    }

    /**
     * 微信小程序注册
     *
     * @param Request $request
     * @return UserResource|void
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function storeByWeappPhone(Request $request)
    {
        $this->validateRequest($request);

        $verifyData = \Cache::get($request->verification_key);
        if (! $verifyData) {
            return $this->response->errorForbidden('验证码已失效');
        }
        if (! hash_equals((string)$verifyData['code'], $request->verification_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        $miniProgram = EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($request->code);

        if (isset($data['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确');
        }

        $user = User::query()->where('weapp_openid', $data['openid'])->first();
        if ($user) {
            return $this->response->errorForbidden('微信已绑定其他账号，请直接登录');
        }

        // 创建用户
        $user = User::query()->create([
            'nickname'          => $data['nickname'],
            'phone'             => $verifyData['phone'],
            'weapp_openid'      => $data['openid'],
            'weapp_session_key' => $data['session_key'],
        ]);

        return (new UserResource($user))->showSensitiveFields();
    }
}
