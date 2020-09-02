<?php

namespace App\Admin\Controllers\V1;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Admin\Resources\AdminRoleResource;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::query()->paginate();

        return AdminRoleResource::collection($roles);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request, $this->storeRequestValidationRules());

        $roleName = $request->input('name');
        $permissions = $request->input('permissions');

        $role = \DB::transaction(function () use ($roleName, $permissions) {
            $role = Role::create(['name' => $roleName]);
            $role->syncPermissions($permissions);

            return $role;
        });

        return new AdminRoleResource($role);
    }

    public function show($id)
    {
        $role = Role::query()->findOrFail($id);

        return new AdminRoleResource($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::query()->findOrFail($id);
        $this->validateRequest($request, $this->updateRequestValidationRules($role->id));

        $name = $request->input('name');
        $permissions = $request->input('permissions');

        $role = \DB::transaction(function () use ($role, $name, $permissions) {
            $role->update(['name' => $name]);
            $role->syncPermissions($permissions);

            return $role;
        });
        return new AdminRoleResource($role);
    }

    public function destroy($id)
    {
        $role = Role::query()->findOrFail($id);
        $role->delete();

        return $this->response->noContent();
    }

    protected function storeRequestValidationRules()
    {
        return [
            'name'        => 'required|string|unique:admin_roles',
            'permissions' => 'required|array',
        ];
    }

    protected function updateRequestValidationRules($roleId)
    {
        return [
            'name'        => 'required|string|unique:admin_roles,name,'.$roleId,
            'permissions' => 'required|array',
        ];
    }
}
