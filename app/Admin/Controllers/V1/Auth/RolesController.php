<?php

namespace App\Admin\Controllers\V1\Auth;

use Illuminate\Http\Request;
use App\Admin\Models\AdminRole;
use App\Admin\Controllers\V1\Controller;
use App\Admin\Resources\AdminRoleResource;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 15);
        $roles = AdminRole::query()->with(['permissions', 'vueMenus'])->paginate($limit);

        return $this->response->success(AdminRoleResource::collection($roles));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $requestData = $request->all();

        $role = \DB::transaction(function () use ($requestData) {
            $role = AdminRole::query()->create(['name' => $requestData['name']]);
            $role->syncPermissions($requestData['permissions']);
            $role->vueMenus()->attach($requestData['menu_ids']);
            return $role;
        });

        $role->load('vueMenus');

        return $this->response->created(new AdminRoleResource($role));
    }

    public function show($id)
    {
        $role = AdminRole::query()->with(['permissions', 'vueMenus'])->findOrFail($id);

        return $this->response->success(new AdminRoleResource($role));
    }

    public function update(Request $request, $id)
    {
        $role = AdminRole::query()->with(['permissions', 'vueMenus'])->findOrFail($id);
        $this->validateRequest($request);

        $requestData = $request->all();

        $role = \DB::transaction(function () use ($role, $requestData) {
            $role->update(['name' => $requestData['name']]);
            $role->syncPermissions($requestData['permissions']);
            $role->vueMenus()->sync($requestData['menu_ids']);

            return $role;
        });

        return $this->response->success(new AdminRoleResource($role));
    }

    public function destroy($id)
    {
        $role = AdminRole::query()->findOrFail($id);
        $role->delete();

        return $this->response->noContent();
    }
}
