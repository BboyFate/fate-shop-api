<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysMaterial;
use App\Models\Systems\SysMaterialGroup;
use App\Admin\Http\Resources\Systems\SysMaterialResource;
use App\Handlers\ImageUploadHandler;

class MaterialsController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysMaterial::query();

        if ($request->input('group_id') && $group = SysMaterialGroup::query()->find($request->input('group_id'))) {
            $builder->whereHas('group', function ($query) use ($group) {
                $query->where('path', 'like', $group->level_path . $group->id . '-%');
            });
        }

        $limit = $request->input('limit', 10);
        $list = $builder->orderBy('id', 'desc')->paginate($limit);

        return $this->response->success(SysMaterialResource::collection($list));
    }

    public function store(Request $request, ImageUploadHandler $uploader)
    {
        $this->validateRequest($request);

        $admin = $request->user();
        $result = $uploader->save($request->file, 'systems/materials', $admin->id);

        $material = new SysMaterial([
            'type' => $request->input('type'),
            'name' => $result['name'],
            'mime' => $result['mime'],
            'path' => $result['path'],
            'size' => $result['size'],
        ]);
        if ($request->group_id) {
            $material->group()->associate($request->group_id);
        }
        $material->save();

        return $this->response->success(new SysMaterialResource($material));
    }

    public function destroy($materialId)
    {
        $material = SysMaterial::query()->findOrFail($materialId);
        $material->delete();

        return $this->response->noContent();
    }

    /**
     * 多个素材删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function materialsDestroy(Request $request)
    {
        $this->validateRequest($request);

        SysMaterial::destroy($request->input('material_ids'));

        return $this->response->noContent();
    }

}
