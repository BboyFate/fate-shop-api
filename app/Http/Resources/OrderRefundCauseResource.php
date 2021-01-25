<?php

namespace App\Http\Resources;

class OrderRefundCauseResource extends BaseResource
{
    public function toArray($request)
    {
        $data = [
            'id'   => $this->id,
            'name' => $this->name,
        ];

        return $data;
    }
}
