<?php

namespace App\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminVueMenuResource extends JsonResource
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
            'id'        => $this->id,
            'name'      => $this->name,
            'path'      => $this->path,
            'redirect'  => $this->redirect,
            'meta'      => $this->meta,
            'component' => $this->component,
            'children'  => AdminVueMenuResource::collection($this->children)
        ];
    }
}
