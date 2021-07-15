<?php

namespace App\Admin\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Admin\Services\SystemService;
use App\Models\Systems\SysPermission;
use App\Models\Systems\SysUser;
use App\Admin\Http\Resources\Systems\SysUserResource;

class MeController extends Controller
{
    public function me(Request $request)
    {
        return $this->response->success(new SysUserResource($request->user()));
    }

    public function meUpdate(Request $request)
    {
        return new SysUserResource($request->user());

        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request->only([
            'username',
            'password',
            'nickname',
            'phone',
        ]), false, $admin);

        return $this->response->success(new SysUserResource($admin));
    }

    protected function updateOrStoreAdmin(Request $request, $isStore = true, $admin = null)
    {
        $attributes = $request->only([
            'nickname',
            'phone',
            'is_enabled',
        ]);

        if ($request->input('password')) {
            $attributes['password'] = Hash::make($request->input('password'));
        }

        $roles = $request->input('roles');

        $admin = \DB::transaction(function () use ($attributes, $roles, $isStore, $admin) {
            if ($isStore) {
                $admin = SysUser::query()->create($attributes);
                $admin->assignRole($roles);
            } else {
                $admin->syncRoles($roles);
                $admin->update($attributes);
            }

            return $admin;
        });

        return $admin;
    }

    /**
     * 当前登录账号可访问的菜单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function menus(Request $request)
    {
        if ($request->user()->isSuperAdmin()) {
            $menus = SysPermission::query()->whereIn('type', [SysPermission::TYPE_MENU, SysPermission::TYPE_DIRECTORY])->get();
        } else {
            $menus = $request->user()->roles()->permissions();
        }

        $result = (new SystemService)->getMenuTree(0, $menus);

        return $this->response->success($result->values());
    }
}
