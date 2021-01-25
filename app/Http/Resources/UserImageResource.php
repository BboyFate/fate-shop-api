<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'path' => $this->path,
        ];
    }
}
