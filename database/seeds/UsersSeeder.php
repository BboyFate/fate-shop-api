<?php

use Illuminate\Database\Seeder;
use App\Models\Users\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(User::class, 100)->create();

        User::query()->where('id', 1)->update(['phone' => 15625662363]);
    }
}
