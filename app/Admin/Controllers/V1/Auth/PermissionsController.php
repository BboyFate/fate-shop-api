<?php

namespace App\Admin\Controllers\V1\Auth;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Admin\Controllers\V1\Controller;
use App\Admin\Resources\AdminPermissionResource;

class PermissionsController extends Controller
{
    public function index()
    {
        $roles = Permission::query()->paginate();

        return AdminPermissionResource::collection($roles);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $permission = Permission::create(['name' => $request->input('name')]);

        return new AdminPermissionResource($permission);
    }

    /**
     * 显示某个账号
     *
     * @param $id
     *
     * @return AdminUserResource|void
     */
    public function show($id)
    {
        $permission = Permission::query()->findOrFail($id);

        return new AdminPermissionResource($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::query()->findOrFail($id);
        $this->validateRequest($request);

        $permission->update(['name' => $request->input('name')]);

        return new AdminPermissionResource($permission);
    }

    public function destroy($id)
    {
        $admin = Permission::query()->findOrFail($id);
        $admin->delete();

        return $this->response->noContent();
    }
}
