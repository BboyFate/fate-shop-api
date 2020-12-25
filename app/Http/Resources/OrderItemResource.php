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
        return [
            'amount'     => $this->amount,
            'rating'     => $this->rating,
            'review'     => $this->review,
            'images'     => $this->images,
            'created_at' => (string)$this->created_at,
            'user'       => new UserResource($this->whenLoaded('user')),
            'sku'        => new ProductSkuResource($this->whenLoaded('productSku')),
        ];
    }
}
