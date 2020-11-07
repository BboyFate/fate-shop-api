<?php

namespace App\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductSkuTemplateResource extends JsonResource
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
            'name'  => $this->name,
            'value' => $this->value,
        ];

        return $data;
    }
}
