<?php

namespace App\Admin\Http\Resources\Products;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkuResource extends JsonResource
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
            'id'         => $this->id,
            'name'       => $this->name,
            'image'      => $this->image,
            'price'      => $this->price,
            'stock'      => (int)$this->stock,
            'weight'     => $this->weight,
            'volume'     => $this->volume,
            'attributes' => $this->attributes,
            'created_at' => (string)$this->created_at,
            'updated_at' => (string)$this->updated_at,
        ];

        return $data;
    }
}
