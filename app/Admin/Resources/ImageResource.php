<?php

namespace App\Admin\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * 默认隐藏显示图片流字段
     *
     * @var bool
     */
    protected $showDataUrlField = false;

    public function toArray($request)
    {
        if ($this->showDataUrlField) {
            $this->resource->append(['data_url' => 'data_url']);
        }

        return parent::toArray($request);
    }

    /**
     * 显示图片流
     *
     * @return $this
     */
    public function showDataUrlField()
    {
        $this->showDataUrlField = true;

        return $this;
    }
}
