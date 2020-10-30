<?php

namespace App\Admin\Validations\V1;

class AuthorizationsValidation
{
    public function store()
    {
        return [
            'rules' => [
                'username' => 'required|string',
                'password' => 'required|alpha_dash|min:6',
            ]
        ];
    }
}
