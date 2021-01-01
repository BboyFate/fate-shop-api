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
            $table->string('type')->default(Order::TYPE_NORMAL)->comment('订单类型');
            $table->string('no')->unique()->comment('订单流水号');
            $table->unsignedBigInteger('user_id');
            $table->json('address')->comment('下单的收货地址');
            $table->decimal('total_amount', 10, 2)->comment('订单总金额');
            $table->string('payment_method')->nullable()->comment('支付方式');
            $table->string('payment_no')->nullable()->comment('支付平台的订单号');
            $table->boolean('closed')->default(false)->comment('订单是否已关闭');
            $table->string('ship_status')->default(Order::SHIP_STATUS_PENDING)->comment('物流状态');
            $table->json('ship_data')->nullable()->comment('物流数据');

            $table->json('extra')->nullable()->comment('其他额外数据');
            $table->text('remark')->nullable()->comment('订单备注');

            $table->dateTime('paid_at')->default(config('app.default_datetime'))->comment('支付时间');
            $table->dateTime('shipped_at')->default(config('app.default_datetime'))->comment('发货时间');
            $table->dateTime('delivered_at')->default(config('app.default_datetime'))->comment('收货时间');
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
