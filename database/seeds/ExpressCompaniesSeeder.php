<?php

use Illuminate\Database\Seeder;
use App\Models\ExpressCompany;
use App\Models\ExpressFee;

class ExpressCompaniesSeeder extends Seeder
{
    public function run()
    {
        $faker = app(Faker\Generator::class);

        $expressCompanyNames = [
            '韵达快运',
        ];
        foreach ($expressCompanyNames as $expressCompanyName) {
            // 创建物流公司
            $company = factory(ExpressCompany::class)->create([
                'name'       => $expressCompanyName,
                'is_default' => true,
            ]);

            // 创建运费模板
            $fee = factory(ExpressFee::class)->create([
                'name'               => '韵达快运运费',
                'is_default'         => true,
                'express_company_id' => $company->id,
            ]);

            // 创建运费模板区域
            $fee->items()->create([
                'provinces' => ['广东省'],
                'fees'      => [
                    'weight' => [
                        'first'     => 20,
                        'first_fee' => 12,
                        'renew_fee' => 0.8,
                    ],
                    'volume' => [
                        'first'     => 20,
                        'first_fee' => 12,
                        'renew_fee' => 0.8,
                    ],
                ],
            ]);
        }
    }
}
