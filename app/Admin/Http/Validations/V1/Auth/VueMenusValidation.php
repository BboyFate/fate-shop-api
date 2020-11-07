<?php

namespace App\Admin\Http\Validations\V1\Auth;

use App\Models\AdminVueMenu;

class VueMenusValidation
{
    public function requestValidation()
    {
        $currentMenuId = request()->id;

        $rules = [
            'path'              => 'required|string',
            'component'         => 'required|string',
            'redirect'          => 'nullable|string',
            'meta'              => 'array',
            'meta.*.icon'       => 'string', // 图标
            'meta.*.title'      => 'string', // 标题
            'meta.*.activeMenu' => 'string', // 调整到当前路径后，高亮某个菜单；这里填写需要高亮的某个菜单 path
            'is_showed'         => 'required|boolean',
            'sorted'            => 'numeric',
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
