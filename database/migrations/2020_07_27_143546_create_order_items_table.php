<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Orders\OrderItem;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sku_id');
            $table->string('shipment_state', 32)->default(OrderItem::SHIPMENT_STATE_PENDING)->comment('子订单运输状态');
            $table->string('receiving_state', 32)->default(OrderItem::RECEIVING_STATE_PENDING)->comment('子订单收货状态');
            $table->string('shipment_refund_state', 32)->default(OrderItem::SHIPMENT_REFUND_STATE_PENDING)->comment('子订单退货状态');
            $table->string('refund_state', 32)->default(OrderItem::REFUND_STATE_PENDING)->comment('子订单退款状态');
            $table->unsignedInteger('qty')->comment('下单的数量');
            $table->unsignedInteger('delivered_qty')->default(0)->comment('已发货数量');
            $table->unsignedInteger('received_qty')->default(0)->comment('已收货数量');
            $table->unsignedInteger('shipment_refunded_qty')->default(0)->comment('已退货的数量');
            $table->unsignedInteger('refunded_qty')->default(0)->comment('已退款的数量');
            $table->decimal('price', 10, 2)->comment('单价');
            $table->decimal('price_total', 10, 2)->comment('原价 qty * price');
            $table->decimal('adjustment_total', 10, 2)->default(0)->comment('调整金额');
            $table->decimal('payment_total', 10, 2)->comment('实际支付价格 price_total + adjustment_total');
            $table->decimal('refunded_total', 10, 2)->default(0)->comment('已退款金额');
            $table->boolean('has_reviewed')->default(false)->comment('订单是否有评价');
            $table->boolean('has_applied_refund')->default(false)->comment('是否有申请退款');
            $table->json('extra')->nullable()->comment('其他额外数据');
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
        Schema::dropIfExists('order_items');
    }
}
