<?php

namespace App\Admin\Controllers\V1;

use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductSkuAttributesController extends Controller
{
    public function format(Request $request, ProductService $service)
    {
        $attributes = $service->formatAttributes($request->input('attributes'));

        return $this->response->success($attributes);
    }
}
