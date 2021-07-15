<?php

namespace App\Admin\Http\Resources\Systems;

use Illuminate\Http\Resources\Json\JsonResource;

class SysDictionaryTypeResource extends JsonResource
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
            'name'         => $this->name,
            'type'         => $this->type,
            'remark'       => $this->remark,
            'is_disabled'  => $this->is_disabled,
            'dictionaries' => SysDictionaryResource::collection($this->whenLoaded('dictionaries')),
            'created_at'   => $this->created_at->toDateTimeString(),
            'updated_at'   => $this->updated_at->toDateTimeString(),
        ];
    }
}
