<?php

namespace App\Http\Validations\V1;

class UsersValidation
{
    public function storeByWeappPhone()
    {
        return [
            'rules' => [
                'phone'             => [
                    'required',
                    'unique:users',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                ],
                'code'              => 'required|string',
                'verification_key'  => 'required|string',
                'verification_code' => 'required|string',
            ],
            'messages' => [
                'phone.unique' => '手机号码已注册了',
            ]
        ];
    }
}
