<?php

namespace App\Admin\Http\Validations\V1;

class CaptchasValidation
{
    public function store()
    {
        return [
            'rules' => [
                'phone' => 'required|phone:CN,mobile|unique:sys_users',
            ]
        ];
    }
}
