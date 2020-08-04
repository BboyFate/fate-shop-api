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
            'user_id'      => $this->user_id,
            'address'      => $this->address,
            'total_amount' => $this->total_amount,
            'remark'       => $this->remark,
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
            'user'         => new UserResource($this->whenLoaded('user')),
            'items'        => new OrderItemResource($this->whenLoaded('items')),
        ];
    }
}
