<?php

namespace App\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $role = $this->roles()->first();
        if ($role) {
            $roles = [$role->name];
        } else {
            $roles = [];
        }

        return [
            'id'          => $this->id,
            'username'    => $this->username,
            'nickname'    => $this->nickname,
            'is_enabled' => $this->is_enabled,
            'phone'       => $this->phone,
            'avatar'      => $this->avatar,
            'roles'       => $roles,
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
