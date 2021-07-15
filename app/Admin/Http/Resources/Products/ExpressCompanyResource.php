<?php

namespace App\Admin\Http\Resources\Products;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpressCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
