<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Orders\Order;

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
            $table->string('type', 32)->default(Order::TYPE_NORMAL)->comment('订单类型：普通类型或众筹等');
            $table->string('no', 64)->unique()->comment('订单流水号');
            $table->string('payment_no', 64)->nullable()->comment('支付平台的订单号');
            $table->string('payment_method', 32)->nullable()->comment('支付方式');
            $table->json('address')->comment('下单的收货地址');
            $table->decimal('adjustment_total', 10, 2)->default(0)->comment('调整优惠或运费等等的价格；负值为优惠，正值为加价');
            $table->decimal('carriage_total', 10, 2)->default(0)->comment('运费');
            $table->decimal('original_total', 10, 2)->comment('订单原价');
            $table->decimal('refunded_total', 10, 2)->default(0)->comment('总订单已退款金额');
            $table->decimal('payment_total', 10, 2)->comment('实际需支付的金额： original_total + item_adjustment_total + adjustment_total');
            $table->unsignedInteger('item_sku_qty')->comment('下单的数量');
            $table->unsignedInteger('delivered_qty')->default(0)->comment('已发货数量');
            $table->unsignedInteger('received_qty')->default(0)->comment('已收货数量');
            $table->unsignedInteger('shipment_refunded_qty')->default(0)->comment('已退货的数量');
            $table->unsignedInteger('refunded_qty')->default(0)->comment('已退款的数量');
            $table->ipAddress('ip_address')->comment('用户IP ip2long');
            $table->string('order_state', 32)->default(Order::ORDER_STATE_PENDING)->comment('订单主要状态');
            $table->string('payment_state', 32)->default(Order::PAYMENT_STATE_PENDING)->comment('支付状态');
            $table->string('shipment_state', 32)->default(Order::SHIPMENT_STATE_PENDING)->comment('运输状态');
            $table->string('receiving_state', 32)->default(Order::RECEIVING_STATE_PENDING)->comment('订单收货状态');
            $table->string('shipment_refund_state', 32)->default(Order::SHIPMENT_REFUND_STATE_PENDING)->comment('订单退货状态');
            $table->boolean('has_applied_refund')->default(false)->comment('是否有申请退款');
            $table->text('remark')->nullable()->comment('订单备注');
            $table->json('extra')->nullable()->comment('其他额外数据');
            $table->dateTime('paid_at')->nullable()->comment('支付时间。订单 item 需要一起支付');
            $table->dateTime('completed_at')->nullable()->comment('订单完成时间');
            $table->dateTime('delivered_at')->nullable()->comment('订单发货时间 这里只记录第一次发货时间');
            $table->dateTime('closed_at')->nullable()->comment('订单关闭时间');
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
