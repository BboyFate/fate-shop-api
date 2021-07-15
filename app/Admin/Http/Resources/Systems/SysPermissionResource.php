<?php

namespace App\Admin\Http\Resources\Systems;

use Illuminate\Http\Resources\Json\JsonResource;

class SysPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'        => $this->id,
            'parent_id' => $this->parent_id,
            'name'      => $this->name,
            'path'      => $this->path,
            'component' => $this->component,
            'meta'      => $this->meta,
            'sorted'    => $this->sorted,
            'is_showed' => $this->is_showed,
        ];

        if (isset($this->children_array)) {
            $data['children'] = $this->children_array;
        }

        return $data;
    }
}
