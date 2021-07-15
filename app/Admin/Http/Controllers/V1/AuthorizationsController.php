<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Systems\SysUser;

class AuthorizationsController extends Controller
{
    /**
     * 账号密码登录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function store(Request $request)
    {
        $this->validateRequest($request);

        $admin = SysUser::query()->where('phone', $request->input('account'))->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            return $this->response->errorUnprocessableEntity('账号或密码错误');
        }
        if ($admin->is_disabled) {
            return $this->response->errorForbidden('账号已冻结');
        }

        $token = Auth::claims(['guard' =>  Auth::getDefaultDriver()])->login($admin);

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
            'token_type'   => 'Bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60  // 单位秒
        ]);
    }
}
