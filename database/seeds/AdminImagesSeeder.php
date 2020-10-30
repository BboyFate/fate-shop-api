<?php

use Illuminate\Database\Seeder;
use App\Admin\Models\AdminImage;

class AdminImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(AdminImage::class, 10)->create();
    }
}
