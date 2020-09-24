<?php

use Illuminate\Database\Seeder;
use App\Admin\Models\AdminUser;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(AdminUser::class, 5)->create();
        $superAdmin = AdminUser::query()->find(1);

        $superAdmin->update([
            'username' => 'fate',
            'nickname' => 'fate',
            'phone'    => 15625662363,
        ]);

        $superAdmin->assignRole(config('app.super_admin_role_name'));
    }
}
