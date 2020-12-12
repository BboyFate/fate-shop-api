<?php

use Illuminate\Database\Seeder;
use App\Models\SystemImage;

class SystemImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(SystemImage::class, 10)->create();
    }
}
