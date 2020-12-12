<?php

namespace App\Http\Validations\V1;

class AuthorizationsValidation extends BaseValidation
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

    public function smsCodeStore()
    {
        return [
            'rules' => [
                'phone'             => $this->rulePhone(),
                'verification_key'  => 'required|string',
                'verification_code' => 'required|string',
            ]
        ];
    }

    public function socialStore()
    {
        $rules = [
            'code'         => 'required_without:access_token|string',
            'access_token' => 'required_without:code|string',
        ];

        if (request()->input('social_type') == 'wechat' && !request()->input('code')) {
            $rules['openid']  = 'required|string';
        }

        return [
            'rules' => $rules
        ];
    }

    public function weappStoreByAccount()
    {
        return [
            'rules' => [
                'phone'     => $this->rulePhone(),
                'password'  => 'required|alpha_dash|min:6',
                'code'      => 'required|string',
                'user_info' => 'required|array'
            ]
        ];
    }

    public function weappStoreBySms()
    {
        return [
            'rules' => [
                'phone'             => $this->rulePhone(),
                'verification_key'  => 'required|string',
                'verification_code' => 'required|string',
                'code'              => 'required|string',
                'user_info'         => 'required|array'
            ]
        ];
    }

    public function bindPhone()
    {
        return [
            'rules' => [
                'phone'             => $this->rulePhone(),
                'bind_key'          => 'required|string',
                'verification_key'  => 'required|string',
                'verification_code' => 'required|string',
            ]
        ];
    }
}
