<?php

namespace App\Admin\Http\Validations\V1\Systems;

use App\Models\Systems\SysMaterial;
use App\Models\Systems\SysMaterialGroup;

class MaterialsValidation
{
    public function store()
    {
        return [
            'rules'    => [
                'type' => 'required|in:' . implode(array_keys(SysMaterial::$typeMap), ','),
            ],
            'messages' => [
                'name.unique' => '分组名称已经存在',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules'    => [
                'group_id' => 'required|exists:' . (new SysMaterialGroup)->getTable() . ',id',
            ],
            'messages' => [
                'name.exists' => '分组名称不存在',
            ]
        ];
    }
}
