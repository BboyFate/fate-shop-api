<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Overtrue\LaravelSocialite\Socialite;
use Overtrue\LaravelWeChat\Facade as EasyWeChat;
use App\Models\User;
use App\Models\UserSocial;
use App\Services\UserService;
use App\Support\Contracts\ResponseConstant;

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
    public function h5StoreByAccount(Request $request)
    {
        $this->validateRequest($request);

        $credentials = [
            'phone' => $request->account,
            'password' => $request->password,
        ];

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->attempt($credentials);
        if (! $token) {
            return $this->response->errorUnauthorized('账号或密码错误');
        }

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 短信验证码登录
     * 未注册的手机号自动生成账号
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function h5StoreBySms(Request $request)
    {
        $this->validateRequest($request);
        $this->validateSmsCode($request->verification_key, $request->verification_code);

        $user = User::query()->where('phone', $request->phone)->first();

        if (empty($user)) {
            $user = User::query()->create([
                'nickname'          => '用户_' . Str::random(8),
                'phone'             => $request->phone,
                'registered_source' => User::REGISTER_H5,
            ]);
        }

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($user);

        $this->forgetSmsCode($request->verification_key);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 第三方登录
     *
     * @param Request $request
     * @param $type 区分是哪个第三方登录
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function socialStore(Request $request, $type)
    {
        $this->validateRequest($request);

        $driver = Socialite::driver($type);

        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = Arr::get($response, 'access_token');
            } else {
                $token = $request->access_token;

                if ($type == 'wechat') {
                    $driver->setOpenId($request->openid);
                }
            }

            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            return $this->response->errorUnauthorized('参数错误，未获取用户信息');
        }

        switch ($type) {
            case 'wechat':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
                $socialBuilder = UserSocial::query()
                    ->where('social_type', UserSocial::TYPE_WECHAT)
                    ->with('user');

                if ($unionid) {
                    $social = $socialBuilder->where('unionid', $unionid)->first();
                } else {
                    $social = $socialBuilder->where('openid', $oauthUser->getId())->first();
                }

                if ($social) {
                    // 已有第三方记录
                    $user = $social->user();
                } else {
                    // 第三方无记录，需要让用户绑定手机号
                    $key = 'bindAccountByPhone_' . Str::random(15);
                    $expiredAt = Carbon::now()->addMinutes(5);  // 5 分钟过期
                    $cacheData = [
                        'social_type'       => UserSocial::TYPE_WECHAT,
                        'openid'            => $oauthUser->getId(),
                        'unionid'           => $oauthUser->$unionid(),
                        'nickname'          => $oauthUser->getNickname(),
                        'avatar'            => $oauthUser->getAvatar(),
                        'registered_source' => User::REGISTER_WECHAT,
                    ];
                    // 绑定相关数据缓存起来
                    \Cache::put($key, $cacheData, $expiredAt);

                    return $this->response->created([
                        'key'        => $key,
                        'expired_at' => $expiredAt->toDateTimeString(),
                    ])->setStatusCode(ResponseConstant::CLIENT_SOCIAL_BIND_ERROR);
                }

                break;
            default:
                return $this->response->errorBadRequest('识别不出登录类型');
        }

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($user);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 微信小程序
     * 账号密码登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function weappStoreByAccount(Request $request)
    {
        $this->validateRequest($request);

        $user = User::query()
            ->where('phone', $request->phone)
            ->with(['socials' => function($query) {
                $query->where('social_type', UserSocial::TYPE_WEAPP);
            }])
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->response->errorUnauthorized('账号或密码错误');
        }

        $weappData = $this->getWeappData($request->code);

        $user = $this->userService->weappLogin($user, $weappData);

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($user);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 微信小程序
     * 手机验证码登录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function weappStoreBySms(Request $request)
    {
        $this->validateRequest($request);
        $this->validateSmsCode($request->verification_key, $request->verification_code);

        $weappData = $this->getWeappData($request->code);

        $user = User::query()->where('phone', $request->phone)->first();

        // 手机号码没有注册过，自动注册账号和绑定该小程序
        if (! $user) {
            $user = User::query()->create([
                'nickname'          => $request->user_info['nickName'],
                'avatar'            => $request->user_info['avatarUrl'],
                'phone'             => $request->phone,
                'registered_source' => User::REGISTER_WEAPP,
            ]);
        }

        $user = $this->userService->weappLogin($user, $weappData);

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($user);

        $this->forgetSmsCode($request->verification_key);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 绑定手机号
     * 第三方登录，如果第三方表没有记录，请求该接口绑定一个账号
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function bindPhoneBySocial(Request $request)
    {
        $this->validateRequest($request);
        $this->validateSmsCode($request->verification_key, $request->verification_code);

        $bindData = \Cache::get($request->bind_key);
        // 判断绑定的用户数据是否已失效
        if (! $bindData) {
            return $this->response->errorForbidden('请重新登录');
        }

        $user = User::query()->where('phone', $request->phone)->first();

        // 没有账号则自动创建一个
        if (! $user) {
            $user = User::query()->create([
                'nickname'          => $bindData['nickname'],
                'avatar'            => $bindData['avatar'],
                'registered_source' => $bindData['registered_source'],
            ]);
        } else {
            // 当前用户已绑定过该第三方平台
            if ($userSocial = $user->socials()->where('social_type', $bindData['social_type'])->first()) {
                $userSocial->delete();
            }
        }

        $user = $this->userService->socialStore($user, $bindData['social_type'], $bindData['openid'], $bindData['extra'], $bindData['unionid']);

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($user);

        $this->forgetSmsCode($request->verification_key);
        \Cache::forget($request->bind_key);

        return $this->respondWithToken($token)->setStatusCode(201);
    }

    /**
     * 获取小程序授权之后的数据
     *
     * @param $code
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string|void
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    protected function getWeappData($code)
    {
        $miniProgram = EasyWeChat::miniProgram();
        $weappData = $miniProgram->auth->session($code);

        // 如果结果错误，说明 code 已过期或不正确，返回 401 错误
        if (isset($weappData['errcode'])) {
            return $this->response->errorUnauthorized('code 不正确');
        }

        return $weappData;
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
}
