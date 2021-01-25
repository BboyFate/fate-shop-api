<?php

namespace App\Http\Resources;

class OrderResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'             => $this->id,
            'no'             => $this->no,
            'address'        => $this->address,
            'original_total' => $this->original_total,
            'payment_total'  => $this->payment_total,
            'qty_item'       => $this->qty_item,
            'is_closed'      => $this->is_closed,
            'order_state'    => $this->order_state,
            'payment_state'  => $this->payment_state,
            'shipment_state' => $this->shipment_state,
            'remark'         => $this->remark,
            'extra'          => $this->extra,
            'paid_at'        => (string)$this->paid_at,
            'completed_at'   => (string)$this->completed_at,
            'created_at'     => (string)$this->created_at,
            'user'           => new UserResource($this->whenLoaded('user')),
            'items'          => OrderItemResource::collection($this->whenLoaded('items')),
        ];

        return $this->filterFields($data);
    }
}
