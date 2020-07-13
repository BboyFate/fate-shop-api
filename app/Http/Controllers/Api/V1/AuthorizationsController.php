<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Account\Models\User;
use App\Account\Services\UserService;

class AuthorizationsController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 账号密码登录
     *
     * @param Request $request
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, $this->authorizationRequestValidationRules());

        if (! $token = $this->userService->phoneOrEmailLogin($request->username, $request->password)) {
            return $this->response->errorUnauthorized('账号或密码错误');
        }

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 短信验证码登录
     *
     * 未注册的手机号自动生成账号
     *
     * @param AuthorizationSmsCodeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function smsCodeStore(Request $request)
    {
        $this->validateRequest($request, $this->smsCodeRequestValidationRules());

        $verifyData = \Cache::get($request->verification_key);
        if (! $verifyData) {
            return $this->response->errorForbidden('验证码已失效');
        }

        if (! hash_equals($verifyData['code'], $request->verification_code)) {
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = $this->userService->phoneRegister($request->phone);
        $token = Auth::login($user);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 第三方登录
     *
     * @param Request $request
     * @param $type 区分是哪个第三方登录
     * @return \Illuminate\Http\JsonResponse
     */
    public function socialStore(Request $request, $type)
    {
        $this->validateRequest($request, $this->socialRequestValidationRules($request));

        $driver = Socialite::driver($type);

        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = Arr::get($response, 'access_token');
            } else {
                $token = $request->access_token;

                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;

                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }

                // 没有用户，默认创建一个用户
                if (! $user) {
                    $user = User::create([
                        'nickname'       => $oauthUser->getNickname(),
                        'avatar'         => $oauthUser->getAvatar(),
                        'weixin_openid'  => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }

                break;
        }

        $token = Auth::login($user);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 刷新 token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        $token = Auth::refresh();

        return $this->respondWithToken($token);
    }

    /**
     * 删除 token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        Auth::logout();

        return $this->response->noContent();
    }

    /**
     * 返回登录成功的 token 信息
     *。
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return $this->response->success([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

    protected function authorizationRequestValidationRules()
    {
        return [
            'username' => 'required|string',
            'password' => 'required|alpha_dash|min:6',
        ];
    }

    protected function smsCodeRequestValidationRules()
    {
        return [
            'phone'             => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
            ],
            'verification_key'  => 'required|string',
            'verification_code' => 'required|string',
        ];
    }

    protected function socialRequestValidationRules(Request $request)
    {
        $rules = [
            'code'         => 'required_without:access_token|string',
            'access_token' => 'required_without:code|string',
        ];

        if ($request->social_type == 'weixin' && !$request->code) {
            $rules['openid']  = 'required|string';
        }

        return $rules;
    }
}
