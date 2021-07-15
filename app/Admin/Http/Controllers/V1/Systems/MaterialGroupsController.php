<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysMaterialGroup;
use App\Admin\Http\Resources\Systems\SysMaterialGroupResource;

class MaterialGroupsController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysMaterialGroup::query();

        $list = $builder->orderBy('id', 'desc')->get();

        return $this->response->success(SysMaterialGroupResource::collection($list));
    }

    public function show($groupId)
    {
        $data = SysMaterialGroup::query()->findOrFail($groupId);

        return $this->response->success(new SysMaterialGroupResource($data));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $data = SysMaterialGroup::query()->create([
            'name' => $request->input('name')
        ]);

        return $this->response->success(new SysMaterialGroupResource($data));
    }

    public function update(Request $request, $groupId)
    {
        $data = SysMaterialGroup::query()->findOrFail($groupId);
        $this->validateRequest($request);

        $data->name = $request->input('name');
        $data->save();

        return $this->response->success(new SysMaterialGroupResource($data));
    }

    public function destroy($groupId)
    {
        $data = SysMaterialGroup::query()->findOrFail($groupId);
        $data->delete();

        return $this->response->noContent();
    }
}
