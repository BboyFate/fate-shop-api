<?php

namespace App\Admin\Controllers\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Admin\Controllers\V1\Controller;
use App\Admin\Models\AdminUser;
use App\Admin\Models\AdminImage;
use App\Admin\Resources\AdminUserResource;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $builder = AdminUser::query();

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('username', 'like', $like)
                    ->orWhere('nickname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $admins = $builder->paginate();

        return AdminUserResource::collection($admins);
    }

    /**
     * 注册管理员账号
     *
     * @param Request $request
     *
     * @return AdminUserResource
     */
    public function store(Request $request)
    {
        $this->validateRequest($request, $this->storeRequestValidationRules($request->user()->id));

        $admin = $this->updateOrStoreAdmin($request);

        return new AdminUserResource($admin);
    }

    public function show($id)
    {
        $admin = AdminUser::query()->findOrFail($id);

        return new AdminUserResource($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = AdminUser::query()->findOrFail($id);
        $this->validateRequest($request, $this->updateRequestValidationRules($admin->id));

        $admin = $this->updateOrStoreAdmin($request, false, $admin);

        return new AdminUserResource($admin);
    }

    protected function updateOrStoreAdmin(Request $request, $isStore = true, $admin = null)
    {
        $attributes = $request->only([
            'username',
            'nickname',
            'phone',
        ]);
        $attributes['password'] = Hash::make($request->input('password'));

        if ($request->avatar_image_id) {
            $image = AdminImage::query()->find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $role = $request->input('role');

        $admin = \DB::transaction(function () use ($attributes, $role, $isStore, $admin) {
            if ($isStore) {
                $admin = AdminUser::query()->create($attributes);
                $admin->assignRole($role);
            } else {
                $admin->syncRoles($role);
                $admin->update($attributes);
            }

            return $admin;
        });

        return $admin;
    }

    public function destroy($id)
    {
        $admin = AdminUser::query()->findOrFail($id);
        $admin->delete();

        return $this->response->noContent();
    }

    protected function storeRequestValidationRules($userId)
    {
        return [
            'username'        => 'required|string|unique:admin_users',
            'password'        => 'required|alpha_dash|min:6|confirmed',
            'nickname'        => 'required|string',
            'phone'           => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                'unique:admin_users'
            ],
            'avatar_image_id' => 'exists:admin_images,id,type,avatar,admin_user_id,' . $userId,
            'role'            => 'required|string',
        ];
    }

    protected function updateRequestValidationRules($userId)
    {
        return [
            'username'        => 'required|string|unique:admin_users,username,'.$userId,
            'password'        => 'required|alpha_dash|min:6|confirmed',
            'nickname'        => 'required|string',
            'phone'           => [
                'required',
                'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                'unique:admin_users,phone,'.$userId
            ],
            'avatar_image_id' => 'exists:admin_images,id,type,avatar,admin_user_id,' . $userId,
            'role'            => 'required|string|exists:admin_roles,name',
        ];
    }
}
