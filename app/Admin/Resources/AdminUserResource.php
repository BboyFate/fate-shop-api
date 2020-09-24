<?php

namespace App\Admin\Resources;

use App\Admin\Models\AdminImage;
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
            'id' => $this->id,
            'username' => $this->username,
            'nickname' => $this->nickname,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'roles' => $roles
        ];
    }
}
