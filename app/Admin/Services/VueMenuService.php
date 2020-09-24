<?php

namespace App\Admin\Services;

use App\Admin\Models\AdminVueMenu;

class VueMenuService
{
    /**
     * 递归 获取菜单
     *
     * @param null $parentId
     * @param null $allMenus
     * @return array
     */
    public function getMenuTree($parentId = null, $allMenus = null)
    {
        if (is_null($allMenus)) {
            return [];
        }

        return $allMenus
            ->where('parent_id', $parentId)
            ->map(function (AdminVueMenu $menu) use ($allMenus) {

                if ($menu->level != 0) {
                    return $menu;
                }

                $menu['children'] = $this->getMenuTree($menu->id, $allMenus);

                return $menu;
            });
    }
}
