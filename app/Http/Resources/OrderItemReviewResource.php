<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemReviewResource extends JsonResource
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
            'rating'      => $this->rating,
            'review'      => $this->review,
            'images'      => $this->images,
            'reviewed_at' => (string) $this->reviewed_at,
            'sku'         => new ProductSkuResource($this->whenLoaded('productSku')),
        ];

        return $data;
    }
}
