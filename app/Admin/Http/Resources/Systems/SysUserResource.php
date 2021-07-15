<?php

namespace App\Admin\Http\Resources\Systems;

use Illuminate\Http\Resources\Json\JsonResource;

class SysUserResource extends JsonResource
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
            'nickname'    => $this->nickname,
            //'is_disabled' => $this->is_disabled,
            'phone'       => $this->phone,
            'roles'       => $this->getRoleNames(),
            'permissions' => $this->isSuperAdmin() ? ['*.*.*'] : $this->getAllPermissions(),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
