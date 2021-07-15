<?php

namespace App\Admin\Http\Resources\Systems;

use Illuminate\Http\Resources\Json\JsonResource;

class SysRoleResource extends JsonResource
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
            'name'           => $this->name,
            'description'    => $this->description,
            'created_at'     => $this->created_at->toDateTimeString(),
            'updated_at'     => $this->updated_at->toDateTimeString(),
            'permission_ids' => $this->permissions->pluck('id'),
        ];
    }
}
