<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SysMaterialsSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(UserAddressesSeeder::class);
        $this->call(ExpressCompaniesSeeder::class);
        $this->call(ProductAttributeTemplatesSeeder::class);
        $this->call(ProductCategoriesSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(OrdersSeeder::class);
        $this->call(OrderRefundCausesSeeder::class);

        $this->call(SysUsersSeeder::class);
        $this->call(SysDictionariesSeeder::class);
    }
}
