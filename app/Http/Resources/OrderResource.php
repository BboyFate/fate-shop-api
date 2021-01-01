<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'no'           => $this->no,
            'address'      => $this->address,
            'total_amount' => $this->total_amount,
            'remark'       => $this->remark,
            'ship_status'  => $this->ship_status,
            'closed'       => $this->closed,
            'payment_no'   => $this->payment_no,
            'created_at'   => (string)$this->created_at,
            'delivered_at' => (string)$this->delivered_at,
            'paid_at'      => (string)$this->paid_at,
            'user'         => new UserResource($this->whenLoaded('user')),
            'items'        => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
