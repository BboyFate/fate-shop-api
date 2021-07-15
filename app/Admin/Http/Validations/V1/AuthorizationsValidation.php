<?php

namespace App\Admin\Http\Validations\V1;

class AuthorizationsValidation
{
    public function store()
    {
        return [
            'rules' => [
                'account' => 'required|string',
                'password' => 'required|alpha_dash|min:6',
            ]
        ];
    }
}
