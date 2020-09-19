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
        $data = parent::toArray($request);
        $avatar = $this->images(AdminImage::TYPE_AVATAR)->latest()->first()->path ?: config('app.image_admin_avatar');

        $data['avatar'] = config('app.url') . $avatar;

        return $data;
    }
}
