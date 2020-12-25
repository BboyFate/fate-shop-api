<?php

namespace App\Http\Resources;

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
            'id'             => $this->id,
            'category_id'    => $this->category_id,
            'title'          => $this->title,
            'long_title'     => $this->long_title,
            'image'          => $this->image,
            'banners'        => $this->banners,
            'rating'         => $this->rating,
            'sold_count'     => $this->sold_count,
            'review_count'   => $this->review_count,
            'price'          => $this->price,
            'description'    => new ProductDescriptionResource($this->whenLoaded('description')),
            'skus'           => ProductSkuResource::collection($this->whenLoaded('skus')),
            'attributes'     => ProductAttributeResource::collection($this->whenLoaded('attributes')),
            'recent_reviews' => OrderItemResource::collection($this->whenLoaded('recentReviews')),
        ];
    }
}
