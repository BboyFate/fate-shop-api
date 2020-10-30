<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    /**
     * 手机号码或邮箱密码登录
     *
     * @param $phoneOrEmail
     * @param $password
     * @return bool
     */
    public function phoneOrEmailLogin($phoneOrEmail, $password)
    {
        filter_var($phoneOrEmail, FILTER_VALIDATE_EMAIL)
            ? $credentials['email'] = $phoneOrEmail
            : $credentials['phone'] = $phoneOrEmail;
        $credentials['password'] = $password;

        return \Auth::attempt($credentials);
    }

    /**
     * @param $phone
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function phoneRegister($phone)
    {
        $user = User::query()->where('phone', $phone)->first();

        if (empty($user)) {
            $user = User::query()->create([
                'nickname' => '用户_' . Str::random(8),
                'phone' => $phone,
            ]);
        }

        return $user;
    }
}
