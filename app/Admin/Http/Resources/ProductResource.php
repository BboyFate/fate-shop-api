<?php

namespace App\Admin\Http\Resources;

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
        $data = [
            'id'           => $this->id,
            'type'         => $this->type,
            'title'        => $this->title,
            'long_title'   => $this->long_title,
            'image'        => $this->image,
            'banners'      => $this->banners,
            'on_sale'      => $this->on_sale,
            'price'        => $this->price,
            'rating'       => $this->rating,
            'review_count' => $this->review_count,
            'sold_count'   => $this->sold_count,
            'category_id'  => $this->category_id,
            'created_at'   => (string)$this->created_at,
            'updated_at'   => (string)$this->updated_at,
            'description'  => new ProductDescriptionResource($this->whenLoaded('description')),
            'category'     => (new ProductCategoryResource($this->whenLoaded('category')))->showFullNameField(),
            'skus'         => ProductSkuResource::collection($this->whenLoaded('skus')),
            'attributes'   => ProductAttributeResource::collection($this->whenLoaded('attributes')),
            'crowdfunding' => new CrowdfundingProductResource($this->whenLoaded('crowdfunding')),
        ];

        return $data;
    }
}
