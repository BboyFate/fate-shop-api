<?php

use Illuminate\Database\Seeder;
use App\Models\OrderRefundCause;

class OrderRefundCausesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $reasons = [
            [
                'name' => '多拍、拍错、不想要',
            ],
            [
                'name' => '不喜欢、效果不好',
            ],
            [
                'name' => '货物与描述不符',
            ],
            [
                'name' => '大小/重量与商品不符',
            ],
            [
                'name' => '生产日期/保质期与商品不符',
            ],
            [
                'name' => '标签/批次/包装/成分等与商品不符',
            ],
            [
                'name' => '商品变质/发霉/有异物',
            ],
            [
                'name' => '质量问题',
            ],
            [
                'name' => '受到商品少件、破损或污渍',
            ],
            [
                'name' => '发错货',
            ],
            [
                'name' => '其他',
            ],
        ];

        $at = \Carbon\Carbon::now();
        $reasons = collect($reasons)->map(function ($reason) use ($at) {
            $reason['created_at'] = $at;
            $reason['updated_at'] = $at;
            return $reason;
        })->toArray();

        OrderRefundCause::query()->insert($reasons);
    }
}
