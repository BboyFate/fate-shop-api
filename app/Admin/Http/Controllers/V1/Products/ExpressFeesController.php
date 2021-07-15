<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Expresses\ExpressFee;
use App\Admin\Http\Resources\Products\ExpressFeeResource;

class ExpressFeesController extends Controller
{
    public function index(Request $request)
    {
        $builder = ExpressFee::query();

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where('name', 'like', $like);
        }

        $limit = $request->input('limit', 10);

        $list = $builder->orderBy('id', 'desc')->paginate($limit);

        return $this->response->success(ExpressFeeResource::collection($list));
    }

    public function show($feeId)
    {
        $data = ExpressFee::query()->with(['items'])->findOrFail($feeId);

        return $this->response->success(new ExpressFeeResource($data));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $data = ExpressFee::query()->create($request->only(['name', 'sorted', 'is_showed', 'is_default']));

        return $this->response->created(new ExpressFeeResource($data));
    }

    public function update(Request $request, $feeId)
    {
        $fee = ExpressFee::query()->findOrFail($feeId);
        $this->validateRequest($request);

        DB::beginTransaction();
        try {
            $fee->update([
                'name'       => $request->input('name'),
                'fee_type'   => $request->input('fee_type'),
                'is_default' => $request->input('is_default'),
            ]);

            // 删除旧的 运费模板 区域
            $fee->items()->delete();
            // 新建 运费模板 区域
            foreach ($request->input('items') as $item) {
                $fee->items()->create([
                    'provinces' => $item['provinces'],
                    'fees'      => $item['fees']
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response->errorInternal($e->getMessage());
        }

        return $this->response->success(new ExpressFeeResource($fee));
    }

    public function destroy($feeId)
    {
        $data = ExpressFee::query()->findOrFail($feeId);
        $data->delete();

        return $this->response->noContent();
    }
}
