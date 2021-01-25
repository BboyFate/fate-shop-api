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
            'rating'     => $this->rating,
            'review'     => $this->review,
            'images'     => $this->images,
            'created_at' => (string)$this->created_at,
            'sku'        => new ProductSkuResource($this->whenLoaded('productSku')),
            'user'       => new UserResource($this->whenLoaded('user')),
        ];

        return $data;
    }
}
