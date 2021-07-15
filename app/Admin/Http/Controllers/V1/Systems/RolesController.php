<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysRole;
use App\Admin\Http\Resources\Systems\SysRoleResource;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysRole::query()->with(['permissions']);

        $limit = $request->input('limit', 10);
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            $builder->where('name', 'like', $like);
        }

        $roles = $builder->paginate($limit);

        return $this->response->success(SysRoleResource::collection($roles));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $requestData = $request->all();

        $role = \DB::transaction(function () use ($requestData) {
            $role = SysRole::query()->create([
                'name'        => $requestData['name'],
                'description' => $requestData['description'],
            ]);

            $role->syncPermissions($requestData['permissions']);

            return $role;
        });

        return $this->response->created(new SysRoleResource($role));
    }

    public function show($ruleId)
    {
        $role = SysRole::query()->findOrFail($ruleId);

        return $this->response->success(new SysRoleResource($role));
    }

    public function update(Request $request, $ruleId)
    {
        $role = SysRole::query()->findOrFail($ruleId);

        $this->validateRequest($request);

        $requestData = $request->all();

        $role = \DB::transaction(function () use ($role, $requestData) {
            $role->update([
                'name'        => $requestData['name'],
                'description' => $requestData['description'],
            ]);

            $role->syncPermissions($requestData['permissions']);

            return $role;
        });

        return $this->response->success(new SysRoleResource($role));
    }

    public function destroy($ruleId)
    {
        $role = SysRole::query()->findOrFail($ruleId);
        $role->delete();

        return $this->response->noContent();
    }
}
