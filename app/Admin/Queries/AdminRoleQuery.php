<?php

namespace App\Admin\Queries;

use App\Admin\Models\AdminRole;
use Spatie\QueryBuilder\QueryBuilder;

class AdminRoleQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(AdminRole::query());

        $this->allowedIncludes('permissions', 'vue_menus')
            ->defaultSort('-created_at');
    }
}
