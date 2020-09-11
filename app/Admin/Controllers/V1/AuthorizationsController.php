<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Admin\Models\AdminUser;

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

        $admin = AdminUser::query()->where([
            'username' => $request->username,
        ])->first();

        if (! Hash::check($request->password, $admin->password)) {
            return $this->response->errorUnprocessableEntity('账号或密码错误');
        }
        if ($admin->is_disabled) {
            return $this->response->errorForbidden('账号无法登录');
        }

        $presentGuard = Auth::getDefaultDriver();
        $token = Auth::claims(['guard' => $presentGuard])
            ->login($admin);

        if (! $token) {
            return $this->response->errorUnauthorized('账号或密码错误');
        }

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
            'expires_in' => Auth::factory()->getTTL() * 60 * 60 * 60
        ]);
    }
}
