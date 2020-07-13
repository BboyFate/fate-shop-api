<?php

namespace App\Account\Services;

use Illuminate\Support\Str;
use App\Account\Repositories\Contracts\UserRepository;

class UserService
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

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
     * @return mixed
     */
    public function phoneRegister($phone)
    {
        $user = $this->repository->getModel()->where('phone', $phone)->first();

        if (empty($user)) {
            $user = $this->repository->create([
                'nickname' => '用户_' . Str::random(8),
                'phone' => $phone,
            ]);
        }

        return $user;
    }
}
