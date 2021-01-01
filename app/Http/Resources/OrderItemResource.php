<?php

namespace App\Http\Resources;

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
        $data = [
            'id'             => $this->id,
            'price'          => $this->price,
            'amount'         => $this->amount,
            'reviewed'       => $this->reviewed,
            'refund_status'  => $this->refund_status,
            'refunded_money' => $this->refunded_money,
            'refunded_at'    => (string)$this->refunded_at,
            'sku'            => new ProductSkuResource($this->whenLoaded('productSku')),
            'order'          => new OrderResource($this->whenLoaded('order')),
        ];

        return $data;
    }
}
