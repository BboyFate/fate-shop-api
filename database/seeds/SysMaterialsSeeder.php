<?php

use Illuminate\Database\Seeder;
use App\Models\Systems\SysMaterial;

class SysMaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(SysMaterial::class, 10)->create();
    }
}
