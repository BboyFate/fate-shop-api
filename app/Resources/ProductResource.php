<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'type'         => $this->type,
            'category_id'  => $this->category_id,
            'title'        => $this->title,
            'long_title'   => $this->long_title,
            'description'  => $this->description,
            'image'        => $this->image,
            'rating'       => $this->rating,
            'sold_count'   => $this->sold_count,
            'review_count' => $this->review_count,
            'price'        => $this->price,
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
            'skus'         => ProductSkuResource::collection($this->whenLoaded('skus')),
        ];
    }
}
