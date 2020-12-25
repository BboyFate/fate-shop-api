<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * 默认隐藏敏感字段
     *
     * @var bool
     */
    protected $showSensitiveFields = false;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (! $this->showSensitiveFields) {
            $this->resource->addHidden(['phone', 'email']);
        }

        $data = [
            'avatar' => $this->avatar,
            'nickname' => $this->nickname,
        ];

        $data['bound_phone'] = $this->resource->phone ? true : false;
        $data['bound_wechat'] = ($this->resource->wechat_unionid || $this->resource->wechat_openid) ? true : false;

        return $data;
    }

    /**
     * 显示敏感字段
     *
     * @return $this
     */
    public function showSensitiveFields()
    {
        $this->showSensitiveFields = true;

        return $this;
    }
}
