<?php

namespace App\Admin\Http\Validations\V1\Systems;

use App\Models\Systems\SysPermission;

class PermissionsValidation
{
    public function requestValidation()
    {
        $rules = [
            'meta'              => 'array',
            'meta.*.icon'       => 'string', // 图标
            'meta.*.title'      => 'string', // 标题
            'meta.*.activeMenu' => 'string', // 调整到当前路径后，高亮某个菜单；这里填写需要高亮的某个菜单 path
            'is_showed'         => 'required|boolean',
            'sorted'            => 'numeric',
        ];

        // 不是 按钮类型 的
        if (request()->input('type') != SysPermission::TYPE_BTN) {
            $rules = array_merge($rules, [
                'path'      => 'required|string',   // 路由路径
                'component' => 'required|string',
            ]);
        }

        switch (request()->method()) {
            case 'POST':
                $rules['name']      = 'required|string|unique:' . (new SysPermission)->getTable();
                $rules['parent_id'] = [
                    'integer',
                    function ($attribute, $value, $fail) {
                        if (!$parent = SysPermission::query()->find($value)) {
                            $fail('上级权限不存在');
                            return;
                        }
                    }];
                break;

            case 'PATCH':
                $rules['name'] = 'required|string|unique:' . (new SysPermission)->getTable() . ',name,' . request()->route('permissionId');
        }

        return [
            'rules'    => $rules,
            'messages' => [
                'name.unique' => '权限唯一名称已存在',
            ]
        ];
    }
}
