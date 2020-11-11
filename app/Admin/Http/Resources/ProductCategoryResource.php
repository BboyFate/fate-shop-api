<?php

namespace App\Admin\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    /**
     * 默认隐藏分类的所有名称这个字段
     *
     * @var bool
     */
    protected $showFullNameField = false;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id'           => $this->id,
            'name'         => $this->name,
            'is_directory' => $this->is_directory,
            'level'        => $this->level,
            'created_at'   => (string) $this->created_at,
            'updated_at'   => (string) $this->updated_at,
        ];

        if ($this->showFullNameField) {
            $data['full_name'] = $this->full_name;
        }

        return $data;
    }

    /**
     * 显示分类的所有名称
     *
     * @return $this
     */
    public function showFullNameField()
    {
        $this->showFullNameField = true;

        return $this;
    }
}
