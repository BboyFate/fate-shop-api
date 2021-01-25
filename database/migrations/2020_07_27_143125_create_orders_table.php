<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');

            $table->string('type')->default(Order::TYPE_NORMAL)->comment('订单类型：普通类型或众筹等');
            $table->string('no')->unique()->comment('订单流水号');
            $table->json('address')->comment('下单的收货地址');

            $table->decimal('adjustment_total', 10, 2)->default(0)->comment('调整优惠或运费等等的价格；负值为优惠，正值为加价');
            $table->decimal('item_adjustment_total', 10, 2)->default(0)->comment('items 表调整优惠或运费等等的价格和');
            $table->decimal('original_total', 10, 2)->comment('订单原价');
            $table->decimal('payment_total', 10, 2)->comment('实际需支付的金额： original_total + item_adjustment_total + adjustment_total');
            $table->unsignedInteger('qty_item')->comment('总下单的商品数量');

            $table->boolean('is_closed')->default(false)->comment('订单是否已关闭');
            $table->string('order_state')->default(Order::ORDER_STATE_PENDING)->comment('订单主要状态');
            $table->string('payment_state')->default(Order::PAYMENT_STATE_PENDING)->comment('支付状态');
            $table->string('shipment_state')->default(Order::SHIPMENT_STATE_PENDING)->comment('运输状态');

            $table->text('remark')->nullable()->comment('订单备注');
            $table->json('extra')->nullable()->comment('其他额外数据');
            $table->ipAddress('ip_address')->comment('用户IP ip2long');
            $table->dateTime('paid_at')->nullable()->comment('支付时间。订单 item 需要一起支付');
            $table->dateTime('completed_at')->nullable()->comment('订单完成时间');
            $table->dateTime('created_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
