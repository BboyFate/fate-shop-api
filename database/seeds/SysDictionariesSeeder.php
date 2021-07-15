<?php

use Illuminate\Database\Seeder;
use App\Models\Systems\SysDictionaryType;

class SysDictionariesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getDictionaries() as $data) {
            $Type = SysDictionaryType::query()->create([
                'name' => $data['name'],
                'type' => $data['type'],
            ]);

            $Type->dictionaries()->createMany($data['dictionaries']);
        }
    }

    protected function getDictionaries()
    {
        return [
            [
                'name' => '正常停用状态',
                'type' => 'normal_disable',
                'dictionaries' => [
                    [
                        'lavel'      => '停用',
                        'value'      => true,
                        'value_type' => 'boolean',
                    ],
                    [
                        'lavel'      => '正常',
                        'value'      => false,
                        'value_type' => 'boolean',
                    ],
                ]
            ],
            [
                'name' => '显示状态',
                'type' => 'normal_show',
                'dictionaries' => [
                    [
                        'lavel'      => '显示',
                        'value'      => true,
                        'value_type' => 'boolean',
                    ],
                    [
                        'lavel'      => '隐藏',
                        'value'      => false,
                        'value_type' => 'boolean',
                    ],
                ]
            ],
            [
                'name' => '默认状态',
                'type' => 'normal_default',
                'dictionaries' => [
                    [
                        'lavel'      => '默认',
                        'value'      => true,
                        'value_type' => 'boolean',
                    ],
                    [
                        'lavel'      => '非默认',
                        'value'      => false,
                        'value_type' => 'boolean',
                    ],
                ]
            ],
            [
                'name' => '权限类型',
                'type' => 'sys_permission_type',
                'dictionaries' => [
                    [
                        'lavel'      => '目录',
                        'value'      => 'directory',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '菜单',
                        'value'      => 'menu',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '按钮',
                        'value'      => 'btn',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '字典值类型',
                'type' => 'sys_dict_value_type',
                'dictionaries' => [
                    [
                        'lavel'      => '布尔值',
                        'value'      => 'boolean',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '字符串',
                        'value'      => 'string',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '整数',
                        'value'      => 'integer',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '商品上架状态',
                'type' => 'product_sale',
                'dictionaries' => [
                    [
                        'lavel'      => '上架',
                        'value'      => true,
                        'value_type' => 'boolean',
                    ],
                    [
                        'lavel'      => '下架',
                        'value'      => false,
                        'value_type' => 'boolean',
                    ],
                ]
            ],
            [
                'name' => '运费计算类型',
                'type' => 'express_fee_type',
                'dictionaries' => [
                    [
                        'lavel'      => '按重量',
                        'value'      => 'weight',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '按件',
                        'value'      => 'volume',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单运输状态',
                'type' => 'order_shipment_state',
                'dictionaries' => [
                    [
                        'lavel'      => '待发货',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '全部发货',
                        'value'      => 'delivered',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '部分发货',
                        'value'      => 'partially_delivered',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '已收货',
                        'value'      => 'received',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单收货状态',
                'type' => 'order_receiving_state',
                'dictionaries' => [
                    [
                        'lavel'      => '无',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '全部收货',
                        'value'      => 'received',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '部分收货',
                        'value'      => 'partially_received',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单退货状态',
                'type' => 'order_shipment_refund_state',
                'dictionaries' => [
                    [
                        'lavel'      => '无',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '全部退货',
                        'value'      => 'refunded',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '部分退货',
                        'value'      => 'partially_refunded',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单退款状态',
                'type' => 'order_refund_state',
                'dictionaries' => [
                    [
                        'lavel'      => '无',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '全部退款',
                        'value'      => 'refunded',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '部分退款',
                        'value'      => 'partially_refunded',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '退款中',
                        'value'      => 'processing',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '拒绝退款',
                        'value'      => 'disagreed',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '退款成功',
                        'value'      => 'succeed',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '退款失败',
                        'value'      => 'failed',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '取消退款',
                        'value'      => 'failed',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单支付状态',
                'type' => 'order_payment_state',
                'dictionaries' => [
                    [
                        'lavel'      => '待支付',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '已支付',
                        'value'      => 'paid',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '全部退款',
                        'value'      => 'refunded',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '部分退款',
                        'value'      => 'partially_refunded',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单退款方式',
                'type' => 'order_refund_method',
                'dictionaries' => [
                    [
                        'lavel'      => '仅退款',
                        'value'      => 'only_refund',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '退货退款',
                        'value'      => 'all',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单状态',
                'type' => 'order_state',
                'dictionaries' => [
                    [
                        'lavel'      => '待支付',
                        'value'      => 'pending',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '新的订单',
                        'value'      => 'new',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '订单取消',
                        'value'      => 'cancelled',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '订单完成',
                        'value'      => 'completed',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单类型',
                'type' => 'order_type',
                'dictionaries' => [
                    [
                        'lavel'      => '普通订单',
                        'value'      => 'normal',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '众筹订单',
                        'value'      => 'crowdfunding',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '秒杀订单',
                        'value'      => 'seckill',
                        'value_type' => 'string',
                    ],
                ]
            ],
            [
                'name' => '订单价格调整类型',
                'type' => 'order_adjustment_type',
                'dictionaries' => [
                    [
                        'lavel'      => '运费',
                        'value'      => 'shipping',
                        'value_type' => 'string',
                    ],
                    [
                        'lavel'      => '促销',
                        'value'      => 'promotion',
                        'value_type' => 'string',
                    ],
                ]
            ],
        ];
    }
}
