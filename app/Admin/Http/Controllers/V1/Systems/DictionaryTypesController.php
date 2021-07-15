<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysDictionaryType;
use App\Admin\Http\Resources\Systems\SysDictionaryTypeResource;

class DictionaryTypesController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysDictionaryType::query();
        $limit = $request->input('limit', 10);

        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            $builder->where('name', 'like', $like);
        }

        if ($types = $request->input('types', [])){
            $builder->whereIn('type', $types);
        }

        $dictionaryTypes = $builder->with(['dictionaries'])->paginate($limit);

        return $this->response->success(SysDictionaryTypeResource::collection($dictionaryTypes));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $dictionaryType = SysDictionaryType::query()->create([
            'name'        => $request->input('name'),
            'type'        => $request->input('type'),
            'is_disabled' => $request->input('is_disabled'),
            'remark'      => $request->input('remark'),
        ]);

        return $this->response->created(new SysDictionaryTypeResource($dictionaryType));
    }

    public function show($typeId)
    {
        $dictionaryType = SysDictionaryType::query()
            ->with('dictionaries')
            ->findOrFail($typeId);

        return $this->response->success(new SysDictionaryTypeResource($dictionaryType));
    }

    /**
     * 根据字典类型搜索
     *
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function getDictionariesFilterType(Request $request)
    {
        $dictionaryType = SysDictionaryType::query()
            ->with('dictionaries')
            ->where('type', $request->input('type', ''))
            ->firstOrFail();

        return $this->response->success(new SysDictionaryTypeResource($dictionaryType));
    }

    /**
     * 查询多个字典类型
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function getDictionariesFilterTypes(Request $request)
    {
        $list = SysDictionaryType::query()
            ->with('dictionaries')
            ->whereIn('type', $request->input('types', []))
            ->get();

        return $this->response->success(SysDictionaryTypeResource::collection($list));
    }

    public function update(Request $request, $typeId)
    {
        $dictionaryType = SysDictionaryType::query()->findOrFail($typeId);

        $this->validateRequest($request);

        $dictionaryType->update([
            'name'        => $request->input('name'),
            'type'        => $request->input('type'),
            'remark'      => $request->input('remark'),
            'is_disabled' => $request->input('is_disabled'),
        ]);

        return $this->response->success(new SysDictionaryTypeResource($dictionaryType));
    }

    public function destroy($typeId)
    {
        $dictionaryType = SysDictionaryType::query()->findOrFail($typeId);
        $dictionaryType->delete();

        return $this->response->noContent();
    }
}
