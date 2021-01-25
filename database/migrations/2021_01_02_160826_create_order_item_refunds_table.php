<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedBigInteger('order_item_id');

            $table->string('type', 32)->comment('退款类型');
            $table->string('refund_no')->unique()->nullable()->comment('退款单号');
            $table->string('refund_state')->default(\App\Models\OrderItemRefund::REFUND_STATE_PENDING)->comment('退款状态');
            $table->unsignedInteger('refunded_qty')->comment('退款数量');
            $table->decimal('refunded_total', 10, 2)->comment('退款金额');

            $table->boolean('is_verified')->default(false)->comment('退款审核');
            $table->json('extra')->nullable()->comment('额外数据');

            $table->dateTime('refunded_at')->comment('退款时间');
            $table->dateTime('refund_verified_at')->nullable()->comment('退款验证时间');
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
