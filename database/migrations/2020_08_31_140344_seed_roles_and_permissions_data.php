<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SeedRolesAndPermissionsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /// 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        config(['auth.defaults.guard' => 'admin']);

        // 创建一个超级管理员
        $role = Role::query()->create(['name' => config('app.super_admin_role_name')]);

        $routes = Route::getRoutes();
        $nowTime = \Illuminate\Support\Carbon::now();
        $permissions = [];
        foreach ($routes as $k => $route) {
            if (strpos($k, 'admin') !== false) {
                if (in_array('auth_refresh', $route['action']['middleware'])) {
                    $permissions[] = [
                        'name'       => $route['action']['as'],
                        'guard_name' => 'admin',
                        'created_at' => $nowTime,
                        'updated_at' => $nowTime,
                    ];
                }
            }
        }
        DB::table(config('permission.table_names.permissions'))->insert($permissions);

        $role->givePermissionTo(Permission::all());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 需清除缓存，否则会报错
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // 清空所有数据表数据
        $tableNames = config('permission.table_names');

        DB::table($tableNames['role_has_permissions'])->delete();
        DB::table($tableNames['model_has_roles'])->delete();
        DB::table($tableNames['model_has_permissions'])->delete();
        DB::table($tableNames['roles'])->delete();
        DB::table($tableNames['permissions'])->delete();
    }
}
