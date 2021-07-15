<?php

namespace App\Admin\Http\Resources\Orders;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['refund'] = new OrderItemRefundResource($this->whenLoaded('refund'));

        return $data;
    }
}
