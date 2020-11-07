<?php

namespace App\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminRoleResource extends JsonResource
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
            'id'          => $this->id,
            'name'        => $this->name,
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
            'vue_menus'   => new AdminVueMenuCollection($this->whenLoaded('vueMenus')),
            'permissions' => new AdminPermissionResource($this->whenLoaded('permissions')),
        ];
    }
}
