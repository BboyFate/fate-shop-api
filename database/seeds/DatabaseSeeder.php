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
        $this->call(UsersSeeder::class);

        $this->call(AdminVueMenusSeeder::class);
        $this->call(AdminUsersSeeder::class);
        $this->call(AdminImagesSeeder::class);

        $this->call(UserAddressesSeeder::class);
        $this->call(ProductCategoriesSeeder::class);
        $this->call(ProductsSeeder::class);
        $this->call(OrdersSeeder::class);
    }
}
