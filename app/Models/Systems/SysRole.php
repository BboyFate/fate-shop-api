<?php

namespace App\Models\Systems;

use Spatie\Permission\Models\Role;

class SysRole extends Role
{
    public function menus()
    {
        return $this->belongsToMany(
            SysMenu::class,
            'sys_role_has_menus',
            'role_id',
            'menu_id'
        );
    }
}
