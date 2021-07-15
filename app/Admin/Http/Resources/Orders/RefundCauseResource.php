<?php

namespace App\Admin\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundCauseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
