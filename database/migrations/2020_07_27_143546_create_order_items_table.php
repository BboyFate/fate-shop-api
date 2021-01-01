<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\OrderItem;

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
            $table->unsignedInteger('amount')->comment('下单的数量');
            $table->decimal('price', 10, 2)->comment('单价');
            $table->decimal('refunded_money', 10, 2)->default(0)->comment('退款金额');
            $table->string('refund_status')->default(OrderItem::REFUND_STATUS_PENDING)->comment('退款状态');
            $table->boolean('is_applied_refund')->default(false)->comment('是否申请退款');
            $table->string('refund_no')->unique()->nullable()->comment('退款单号');
            $table->json('extra')->nullable()->comment('其他额外数据');
            $table->boolean('reviewed')->default(false)->comment('订单是否已评价');

            $table->dateTime('refunded_at')->default(config('app.default_datetime'))->comment('退款时间');
            $table->dateTime('refund_verified_at')->default(config('app.default_datetime'))->comment('退款验证时间');
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
