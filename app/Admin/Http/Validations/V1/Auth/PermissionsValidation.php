<?php

namespace App\Admin\Http\Validations\V1\Auth;

class PermissionsValidation
{
    public function store()
    {
        return [
            'rules' => [
                'name' => 'required|string|unique:admin_permissions',
            ]
        ];
    }

    public function update()
    {
        return [
            'rules' => [
                'name' => 'required|string|unique:admin_permissions,name,' . request()->route('id'),
            ]
        ];
    }
}
