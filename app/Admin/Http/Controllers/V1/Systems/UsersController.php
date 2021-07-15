<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysUser;
use App\Admin\Http\Resources\Systems\SysUserResource;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysUser::query();
        $limit = $request->input('limit', 10);

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('nickname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $admins = $builder->paginate($limit);

        return $this->response->success(SysUserResource::collection($admins));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request);

        return $this->response->created(new SysUserResource($admin));
    }

    public function show($id)
    {
        $admin = SysUser::query()->findOrFail($id);

        return $this->response->success(new SysUserResource($admin));
    }

    public function update(Request $request, $id)
    {
        $admin = SysUser::query()->findOrFail($id);
        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request, false, $admin);

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

    public function destroy($id)
    {
        $admin = SysUser::query()->findOrFail($id);
        $admin->delete();

        return $this->response->noContent();
    }
}
