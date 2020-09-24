<?php

namespace App\Admin\Validations\V1\Auth;

use App\Admin\Models\AdminVueMenu;

class VueMenusValidation
{
    public function requestValidation()
    {
        $rules = [
            'name'     => 'required|string|unique:admin_vue_menus',
            'path'     => 'required|string',
            'redirect' => 'string',
            'meta'     => 'array',
        ];

        switch (request()->method()) {
            case 'POST':
                $rules['parent_id'] = [
                    'integer',
                    function ($attribute, $value, $fail) {
                        if (!$parent = AdminVueMenu::query()->find($value)) {
                            $fail('该父菜单不存在');
                            return;
                        }
                    }];
                break;

            case 'PATCH':

        }

        return [
            'rules' => $rules
        ];
    }
}
