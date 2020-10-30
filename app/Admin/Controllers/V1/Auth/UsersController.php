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
        $limit = $request->input('limit', 15);

        // 关键词搜索
        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where(function ($query) use ($like) {
                $query->where('username', 'like', $like)
                    ->orWhere('nickname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $admins = $builder->paginate($limit);

        return $this->response->success(AdminUserResource::collection($admins));
    }

    public function me(Request $request)
    {
        return $this->response->success(new AdminUserResource($request->user()));
    }

    public function meUpdate(Request $request)
    {
        return new AdminUserResource($request->user());

        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request, false, $admin);

        return $this->response->success(new AdminUserResource($admin));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request);

        return $this->response->created(new AdminUserResource($admin));
    }

    public function show($id)
    {
        $admin = AdminUser::query()->findOrFail($id);

        return $this->response->success(new AdminUserResource($admin));
    }

    public function update(Request $request, $id)
    {
        $admin = AdminUser::query()->findOrFail($id);
        $this->validateRequest($request);

        $admin = $this->updateOrStoreAdmin($request, false, $admin);

        return $this->response->success(new AdminUserResource($admin));
    }

    protected function updateOrStoreAdmin(Request $request, $isStore = true, $admin = null)
    {
        $attributes = $request->only([
            'username',
            'nickname',
            'phone',
            'is_enabled',
        ]);

        if ($request->input('password')) {
            $attributes['password'] = Hash::make($request->input('password'));
        }

        if ($request->avatar_image_id) {
            $image = AdminImage::query()->find($request->avatar_image_id);
            $attributes['avatar'] = $image->path;
        }

        $roles = $request->input('roles');

        $admin = \DB::transaction(function () use ($attributes, $roles, $isStore, $admin) {
            if ($isStore) {
                $admin = AdminUser::query()->create($attributes);
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
        $admin = AdminUser::query()->findOrFail($id);
        $admin->delete();

        return $this->response->noContent();
    }
}
