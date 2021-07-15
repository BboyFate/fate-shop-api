<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysPermission;
use App\Admin\Services\SystemService;
use App\Admin\Http\Resources\Systems\SysPermissionResource;

class PermissionsController extends Controller
{
    public function index()
    {
        $permissions = SysPermission::query()->get();

        $result = (new SystemService)->getMenuTree(0, $permissions);

        return $this->response->success($result->values());
    }

    public function store(Request $request)
    {
        $this->validateRequest($request, 'requestValidation');

        $data = new SysPermission($request->only([
            'type',
            'name',
            'path',
            'meta',
            'sorted',
            'is_showed',
            'component',
        ]));

        if ($request->input('parent_id')) {
            $data->parent()->associate($request->input('parent_id'));
        }

        $data->save();

        return $this->response->created(new SysPermissionResource($data));
    }

    public function show($permissionId)
    {
        $data = SysPermission::query()->findOrFail($permissionId);

        return $this->response->success(new SysPermissionResource($data));
    }

    public function update(Request $request, $permissionId)
    {
        $menu = SysPermission::query()->findOrFail($permissionId);
        $this->validateRequest($request, 'requestValidation');

        $menu->update([
            'name'      => $request->input('name'),
            'path'      => $request->input('path'),
            'meta'      => $request->input('meta'),
            'is_showed' => $request->input('is_showed'),
            'component' => $request->input('component'),
        ]);

        return $this->response->success(new SysPermissionResource($menu));
    }

    public function destroy($permissionId)
    {
        $data = SysPermission::query()->findOrFail($permissionId);
        $data->delete();

        return $this->response->noContent();
    }
}
