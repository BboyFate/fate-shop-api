<?php

namespace App\Admin\Services;

use App\Models\Systems\SysPermission;

class SystemService
{
    /**
     * 递归 获取菜单
     *
     * @param null $parentId
     * @param null $allMenus
     * @return array
     */
    public function getMenuTree($parentId = 0, $allMenus = null)
    {
        if (is_null($allMenus)) {
            return [];
        }

        return $allMenus
            ->where('parent_id', $parentId)
            ->map(function (SysPermission $menu) use ($allMenus) {
                $data = [
                    'id'         => $menu->id,
                    'name'       => $menu->name,
                    'path'       => $menu->path,
                    'component'  => $menu->component,
                    'meta'       => $menu->meta,
                    'type'       => $menu->type,
                    'is_showed'  => $menu->is_showed,
                    'sorted'     => $menu->sorted,
                    'created_at' => (string)$menu->created_at,
                    'updated_at' => (string)$menu->updated_at,
                ];

//                if ($menu->type !== SysPermission::TYPE_DIRECTORY) {
//                    return $data;
//                }

                $data['children'] = $this->getMenuTree($menu->id, $allMenus)->values();

                return $data;
            });
    }
}
