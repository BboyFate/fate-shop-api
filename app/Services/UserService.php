<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserSocial;

class UserService
{
    /**
     * 微信小程序登录逻辑
     *
     * @param User $user
     * @param $weappData
     * @return User
     * @throws \Exception
     */
    public function weappLogin(User $user, $weappData)
    {
        $socialConditions = [
            'social_type' => UserSocial::TYPE_WEAPP,
            'openid'      => $weappData['openid'],
        ];
        $social = UserSocial::query()->where($socialConditions)->first();

        if ($social) {
            if ($social->user_id != $user->id) {
                // 当前第三方已绑定过其他用户，需要删除
                // 当前第三方关联到当前用户
                $social->user()->associate($user);
                $social->save([
                    'extra' => ['weapp_session_key' => $weappData['session_key']]
                ]);
            }
        } else {
            $userSocial = $user->socials()->where('social_type', UserSocial::TYPE_WEAPP)->first();

            // 当前用户已绑定过该第三方平台
            if ($userSocial) {
                $userSocial->delete();
            }

            $user = $this->socialStore($user, UserSocial::TYPE_WEAPP, $weappData['openid'], ['weapp_session_key' => $weappData['session_key']]);
        }

        return $user;
    }

    /**
     * 用户账号创建第三方关联
     *
     * @param User $user
     * @param $socialType 第三方平台
     * @param $openid 第三方唯一ID
     * @param null $extra 其他数据
     * @param string $unionid 第三方平台关联唯一ID
     * @return User
     */
    public function socialStore(User $user, $socialType, $openid, $extra = null, $unionid = '')
    {
        $user->socials()->create([
            'social_type' => $socialType,
            'openid'      => $openid,
            'extra'       => $extra,
            'unionid'     => $unionid
        ]);

        return $user;
    }
}
