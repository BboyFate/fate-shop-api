<?php

namespace App\Admin\Http\Resources\Systems;

use Illuminate\Http\Resources\Json\JsonResource;

class SysDictionaryResource extends JsonResource
{
    protected $isChangeValueField = true;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->isChangeValueField) {
            if ($this->value_type === 'boolean') {
                $this->value = boolval($this->value);
            }
        }

        return [
            'id'          => $this->id,
            'lavel'       => $this->lavel,
            'value'       => $this->value,
            'value_type'  => $this->value_type,
            'is_disabled' => $this->is_disabled,
            'is_default'  => $this->is_default,
            'sorted'      => $this->sorted,
            'remark'      => $this->remark,
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
