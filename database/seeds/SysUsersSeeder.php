<?php

use Illuminate\Database\Seeder;
use App\Models\Systems\SysUser;

class SysUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(SysUser::class, 5)->create();
        $superAdmin = SysUser::query()->find(1);

        $superAdmin->update([
            'nickname' => 'fate',
            'phone'    => 15625662363,
        ]);

        $superAdmin->assignRole(config('app.super_admin_name'));
    }
}
