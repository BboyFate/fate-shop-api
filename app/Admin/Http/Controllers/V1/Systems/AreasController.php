<?php

namespace App\Admin\Http\Controllers\V1\Systems;

use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Systems\SysAreaProvince;
use App\Models\Systems\SysAreaCity;
use App\Models\Systems\SysAreaDistrict;

class AreasController extends Controller
{
    public function getProvinces()
    {
        $list = SysAreaProvince::query()->get();

        return $this->response->success($list);
    }

    public function getCities($provinceCode)
    {
        $list = SysAreaCity::query()->where('province_code', $provinceCode)->get();

        return $this->response->success($list);
    }

    public function getDistricts($cityCode)
    {
        $list = SysAreaDistrict::query()->where('city', $cityCode)->get();

        return $this->response->success($list);
    }
}
