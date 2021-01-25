<?php

namespace App\Http\Resources;

class ProductSkuResource extends BaseResource
{
    public function toArray($request)
    {
        $data = [
            'id'         => $this->id,
            'product_id' => $this->product_id,
            'name'       => $this->name,
            'image'      => $this->image,
            'price'      => $this->price,
            'stock'      => $this->stock,
            'attributes' => $this->attributes,
        ];

        return $this->filterFields($data);
    }
}
