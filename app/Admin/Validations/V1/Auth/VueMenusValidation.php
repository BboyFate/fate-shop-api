<?php

namespace App\Admin\Validations\V1\Auth;

use App\Admin\Models\AdminVueMenu;

class VueMenusValidation
{
    public function requestValidation()
    {
        $currentMenuId = request()->id;

        $rules = [
            'path'      => 'required|string',
            'redirect'  => 'nullable|string',
            'meta'      => 'array',
            'is_showed' => 'required|boolean',
        ];

        switch (request()->method()) {
            case 'POST':
                $rules['name'] = 'required|string|unique:admin_vue_menus';
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
                $rules['name'] = 'required|string|unique:admin_vue_menus,name,' . $currentMenuId;
        }

        return [
            'rules' => $rules
        ];
    }
}
