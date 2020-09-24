<?php

namespace App\Admin\Validations\V1\Auth;

class RolesValidation
{
    public function store()
    {
        return [
            'rules' => [
                'name'        => 'required|string|unique:admin_roles',
                'permissions' => 'required|array',
                'menu_ids'    => 'array',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules' => [
                'name'        => 'required|string|unique:admin_roles,name,'. request()->route('id'),
                'permissions' => 'required|array',
                'menu_ids'    => 'array',
            ]
        ];
    }
}
