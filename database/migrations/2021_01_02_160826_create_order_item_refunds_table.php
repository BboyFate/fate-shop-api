<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Orders\OrderItemRefund;

class CreateOrderItemRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_refunds', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_item_id')->comment('子订单 外键');
            $table->string('refund_no')->unique()->nullable()->comment('退款单号');
            $table->string('thirdparty_no')->unique()->nullable()->comment('第三方退款单号');
            $table->string('refund_method', 32)->comment('退款方式');
            $table->string('refund_state')->default(OrderItemRefund::REFUND_STATE_PENDING)->comment('退款状态');
            $table->unsignedInteger('apply_qty')->comment('退款数量');
            $table->decimal('apply_total', 10, 2)->comment('退款金额');
            $table->unsignedBigInteger('express_company_id')->default(0)->comment('物流公司 外键');
            $table->string('shipment_state')->default(OrderItemRefund::SHIPMENT_STATE_PENDING)->comment('买家退货状态');
            $table->string('express_no')->nullable()->comment('物流单号');
            $table->json('extra')->nullable()->comment('额外数据');
            $table->dateTime('applied_at')->comment('申请退款时间');
            $table->dateTime('agreed_at')->nullable()->comment('同意退款时间');
            $table->dateTime('disagreed_at')->nullable()->comment('拒绝退款时间');
            $table->dateTime('delivered_at')->nullable()->comment('买家发货时间');
            $table->dateTime('received_at')->nullable()->comment('仓库收货时间');
            $table->dateTime('cancelled_at')->nullable()->comment('取消申请时间');
            $table->dateTime('refunded_at')->nullable()->comment('退款时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_refunds');
    }
}
