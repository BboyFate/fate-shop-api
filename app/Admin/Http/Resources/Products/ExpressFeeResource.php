<?php

namespace App\Admin\Http\Resources\Products;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpressFeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = parent::toArray($request);

        $data['items'] = ExpressFeeItemResource::collection($this->whenLoaded('items'));

        return $data;
    }
}
