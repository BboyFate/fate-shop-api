<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysDictionary;
use App\Admin\Http\Resources\Systems\SysDictionaryResource;

class DictionariesController extends Controller
{
    public function index(Request $request)
    {
        $builder = SysDictionary::query()->with(['types']);

        $limit = $request->input('limit', 10);

        $dictionaries = $builder->paginate($limit);

        return $this->response->success(SysDictionaryResource::collection($dictionaries));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $typeId = $request->input('type_id');
            $dictionary = SysDictionary::query()->make($request->only([
                'lavel', 'value', 'value_type', 'sorted', 'is_default', 'is_disabled',
            ]));
            $dictionary->type()->associate($typeId);
            $dictionary->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response->errorInternal($e->getMessage());
        }

        return $this->response->created(new SysDictionaryResource($dictionary));
    }

    public function show($dictionaryId)
    {
        $data = SysDictionary::query()->findOrFail($dictionaryId);

        return $this->response->success(new SysDictionaryResource($data));
    }

    public function update(Request $request, $dictionaryId)
    {
        $data = SysDictionary::query()->findOrFail($dictionaryId);

        $this->validateRequest($request);

        $data->update($request->only([
            'lavel', 'value', 'value_type', 'sorted', 'is_default', 'is_disabled', 'remark'
        ]));

        return $this->response->success(new SysDictionaryResource($data));
    }

    public function destroy($dictionaryId)
    {
        $data = SysDictionary::query()->findOrFail($dictionaryId);
        $data->delete();

        return $this->response->noContent();
    }
}
