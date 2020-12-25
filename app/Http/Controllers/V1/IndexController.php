<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\ProductCategory;
use App\Services\ProductService;

class IndexController extends Controller
{
    /**
     * 小程序首页数据
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     */
    public function weappIndex(Request $request, ProductService $service)
    {
        $categories = ProductCategory::query()->orderBy('sorted', 'desc')->limit(10)->get(['id', 'name', 'image']);
        $banners = Banner::query()->where('type', Banner::TYPE_WEAPP)->orderBy('sorted', 'desc')->get();

        $responses = [
            'categories'   => $categories,
            'banners'      => $banners,
        ];

        return $this->response->success($responses);
    }
}
