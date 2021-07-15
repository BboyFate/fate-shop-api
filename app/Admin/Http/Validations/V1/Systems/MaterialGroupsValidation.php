<?php

namespace App\Admin\Http\Validations\V1\Systems;

use App\Models\Systems\SysMaterialGroup;

class MaterialGroupsValidation
{
    public function store()
    {
        return [
            'rules'    => [
                'name' => 'required|string|unique:' . (new SysMaterialGroup())->getTable(),
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
                'name'        => 'required|string|unique:' . (new SysMaterialGroup())->getTable() . ',name,' . request()->route('groupId'),
            ],
            'messages' => [
                'name.unique' => '分组名称已经存在',
            ]
        ];
    }
}
