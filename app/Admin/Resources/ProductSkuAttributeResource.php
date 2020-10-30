<?php

namespace App\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkuAttributeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        $data = [
            'name'  => $this->name,
            'value' => $this->value,
        ];

        return $data;
    }
}
