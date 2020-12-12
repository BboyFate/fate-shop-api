<?php

namespace App\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
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
            'id'         => $this->id,
            'name'       => $this->name,
            'full_name'  => $this->full_name,
            'parent_id'  => $this->parent_id,
            'level'      => $this->level,
            'is_showed'  => $this->is_showed,
            'image'      => $this->image,
            'sorted'     => $this->sorted,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];

        if ($this->children) {
            $data['children'] = $this->children;
        }

        return $data;
    }
}
