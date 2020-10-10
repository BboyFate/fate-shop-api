<?php

namespace App\Admin\Validations\V1\Auth;

class UsersValidation
{
    public function store()
    {
        return [
            'rules' => [
                'username'        => 'required|string|unique:admin_users',
                'password'        => 'required|alpha_dash|min:6|confirmed',
                'nickname'        => 'required|string',
                'is_enabled'      => 'required|boolean',
                'phone'           => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                    'unique:admin_users'
                ],
                'avatar_image_id' => 'exists:admin_images,id,type,avatar,admin_user_id,' . request()->user()->id,
                'roles'           => 'required',
            ]
        ];
    }

    public function update()
    {
        $currentAdminId = request()->user()->id;

        return [
            'rules' => [
                'username'        => 'required|string|unique:admin_users,username,' . $currentAdminId,
                'password'        => 'alpha_dash|min:6|confirmed',
                'nickname'        => 'required|string',
                'is_enabled'      => 'required|boolean',
                'phone'           => [
                    'required',
                    'regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199)\d{8}$/',
                    'unique:admin_users,phone,' . $currentAdminId
                ],
                'avatar_image_id' => 'exists:admin_images,id,type,avatar,admin_user_id,' . $currentAdminId,
                'roles'           => 'required',
            ]
        ];
    }
}
