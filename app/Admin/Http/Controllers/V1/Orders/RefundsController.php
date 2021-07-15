<?php

namespace App\Admin\Http\Controllers\V1\Orders;

use Illuminate\Http\Request;
use App\Admin\Http\Controllers\V1\Controller;
use App\Models\Orders\OrderRefundCause;
use App\Admin\Http\Resources\Orders\RefundCauseResource;

class RefundsController extends Controller
{
    public function getCauses(Request $request)
    {
        $list = OrderRefundCause::query()->orderBy('sorted', 'desc')->get();

        return $this->response->success(RefundCauseResource::collection($list));
    }
}
