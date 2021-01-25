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
            'id'                => $this->id,
            'qty'               => $this->qty,
            'price'             => $this->price,
            'price_total'       => $this->price_total,
            'adjustment_total'  => $this->adjustment_total,
            'is_reviewed'       => $this->is_reviewed,
            'is_applied_refund' => $this->is_applied_refund,
            'sku'               => new ProductSkuResource($this->whenLoaded('productSku')),
            'order'             => new OrderResource($this->whenLoaded('order')),
            'shipment'          => new OrderItemShipmentResource($this->whenLoaded('shipment')),
            'refund'            => new OrderItemRefundResource($this->whenLoaded('refund')),
        ];

        return $data;
    }
}
