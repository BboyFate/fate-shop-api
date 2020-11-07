<?php

namespace App\Admin\Services;

use App\Models\AdminVueMenu;
use App\Admin\Http\Resources\AdminVueMenuResource;

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

                $data = new AdminVueMenuResource($menu);

                $hasChildren = $allMenus->contains('parent_id', $menu->id);
                if ($hasChildren) {
                    $data['children_array'] = $this->getMenuTree($menu->id, $allMenus)->toArray();
                }

                return $data;
            });
    }
}
