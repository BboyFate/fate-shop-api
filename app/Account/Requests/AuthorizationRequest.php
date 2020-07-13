<?php

namespace App\Http\Requests\Api\V1;

class AuthorizationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string',
            'password' => 'required|alpha_dash|min:6',
        ];
    }

    public function attributes()
    {
        return [
            'username' => '手机号或邮箱',
            'password' => '密码',
            'password.min' => '密码最少 6 位数',
        ];
    }
}
