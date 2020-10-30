<?php

namespace App\Admin\Models;

use Spatie\Permission\Models\Role;

class AdminRole extends Role
{
    public function vueMenus()
    {
        return $this->belongsToMany(
            AdminVueMenu::class,
            'admin_role_has_menus',
            'role_id',
            'menu_id'
        );
    }
}
