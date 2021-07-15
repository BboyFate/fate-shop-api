<?php

namespace App\Admin\Http\Controllers\V1\Products;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Expresses\ExpressCompany;
use App\Admin\Http\Resources\Products\ExpressCompanyResource;

class ExpressCompaniesController extends Controller
{
    public function index(Request $request)
    {
        $builder = ExpressCompany::query();

        if ($search = $request->input('search', '')) {
            $like = '%' . $search . '%';
            $builder->where('name', 'like', $like);
        }

        $limit = $request->input('limit', 10);

        $list = $builder->orderBy('id', 'desc')->paginate($limit);

        return $this->response->success(ExpressCompanyResource::collection($list));
    }

    public function show($companyId)
    {
        $data = ExpressCompany::query()->findOrFail($companyId);

        return $this->response->success(new ExpressCompanyResource($data));
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);

        $data = ExpressCompany::query()->create($request->only(['name', 'sorted', 'is_showed', 'is_default']));

        return $this->response->created(new ExpressCompanyResource($data));
    }

    public function update(Request $request, $companyId)
    {
        $data = ExpressCompany::query()->findOrFail($companyId);
        $this->validateRequest($request);

        $data->update($request->only(['name', 'sorted', 'is_showed', 'is_default']));

        return $this->response->success(new ExpressCompanyResource($data));
    }

    public function destroy($companyId)
    {
        $data = ExpressCompany::query()->findOrFail($companyId);
        $data->delete();

        return $this->response->noContent();
    }
}
