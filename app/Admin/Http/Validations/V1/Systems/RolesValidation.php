<?php

namespace App\Admin\Http\Validations\V1\Systems;

class RolesValidation
{
    public function store()
    {
        return [
            'rules'    => [
                'name'        => 'required|regex:/^[A-Za-z\_]+$/|unique:' . config('permission.table_names')['roles'],
                'description' => 'required|string',
                'permissions' => 'array',
            ],
            'messages' => [
                'name.regex'  => '角色标识只能为字母和“-”',
                'name.unique' => '角色标识已存在',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules'    => [
                'name'        => 'required|string|unique:' . config('permission.table_names')['roles'] . ',name,' . request()->route('ruleId'),
                'description' => 'required|string',
                'permissions' => 'array',
            ],
            'messages' => [
                'name.regex'  => '角色标识只能为字母和“-”',
                'name.unique' => '角色标识已存在',
            ]
        ];
    }
}
