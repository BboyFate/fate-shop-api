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
            $table->unsignedBigInteger('shipment_id')->default(0)->comment('运输 外键');
            $table->unsignedInteger('qty')->comment('下单的数量');
            $table->decimal('price', 10, 2)->comment('单价');
            $table->decimal('price_total', 10, 2)->comment('单价 * 下单的数量');
            $table->decimal('adjustment_total', 10, 2)->default(0)->comment('优惠或运费等等的价格');
            $table->boolean('is_reviewed')->default(false)->comment('订单是否已评价');
            $table->boolean('is_applied_refund')->default(false)->comment('是否申请退款');
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
