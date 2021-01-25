<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemRefundResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id'                 => $this->id,
            'refund_no'          => $this->refund_no,
            'refund_state'       => $this->refund_state,
            'refunded_qty'       => $this->refunded_qty,
            'refunded_total'     => $this->refunded_total,
            'is_verified'        => $this->is_verified,
            'extra'              => $this->extra,
            'refunded_at'        => (string)$this->refunded_at,
            'refund_verified_at' => (string)$this->refund_verified_at,
            'order_item'         => new OrderItemResource($this->whenLoaded('orderItem')),
        ];

        return $data;
    }
}
