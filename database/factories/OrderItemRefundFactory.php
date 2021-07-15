<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Orders\OrderItemRefund;
use App\Models\Expresses\ExpressCompany;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(OrderItemRefund::class, function (Faker $faker) {
    $data = [
        'refund_no' => OrderItemRefund::getAvailableRefundNo(),
        'applied_at' => now(),
    ];

    // 随机生成退款方式
    if (random_int(0, 9) < 5) {
        $data['refund_method'] = OrderItemRefund::REFUND_METHOD_ONLY_REFUND;
    } else {
        $data['refund_method'] = OrderItemRefund::REFUND_METHOD_ALL;
    }

    // 随机取消申请
    if (random_int(0, 9) < 3) {
        $data['refund_state'] = OrderItemRefund::REFUND_STATE_CANCELLED;
        $data['cancelled_at'] = now('-5 days');
    } else {
        if ($data['refund_method'] === OrderItemRefund::REFUND_METHOD_ALL) {
            // 随机买家已发货
            if (random_int(0, 9) < 8) {
                $expressCompany = ExpressCompany::query()->inRandomOrder()->first();

                $data['shipment_state']     = OrderItemRefund::SHIPMENT_STATE_DELIVERED;
                $data['delivered_at']       = now('-4 days');
                $data['express_company_id'] = $expressCompany->id;
                $data['express_no'] = $faker->uuid;

                // 随机仓库已收到货
                if (random_int(0, 9) < 7) {
                    $data['shipment_state'] = OrderItemRefund::SHIPMENT_STATE_RECEIVED;
                    $data['received_at']    = now('-3 days');

                    // 随机同意退款
                    if (random_int(0, 9) < 8) {
                        $data['thirdparty_no'] = $faker->uuid;
                        $data['refund_state']  = OrderItemRefund::REFUND_STATE_SUCCEED;
                        $data['agreed_at']     = now('-2 days');
                        $data['refunded_at']   = now('-3 days');
                    } else {
                        $data['refund_state'] = OrderItemRefund::REFUND_STATE_DISAGREED;
                        $data['disagreed_at'] = now('-5 days');
                    }
                }
            }
        } else {
            // 随机同意退款
            if (random_int(0, 9) < 8) {
                $data['thirdparty_no'] = $faker->uuid;
                $data['refund_state']  = OrderItemRefund::REFUND_STATE_SUCCEED;
                $data['agreed_at']     = now('-2 days');
                $data['refunded_at']   = now('-3 days');
            } else if (random_int(0, 9) < 5) {
                $data['refund_state'] = OrderItemRefund::REFUND_STATE_DISAGREED;
                $data['disagreed_at'] = now('-5 days');
            }
        }
    }

    return $data;
});
