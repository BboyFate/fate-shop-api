<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_adjustments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->default(0);
            $table->unsignedBigInteger('order_item_id')->default(0);
            $table->unsignedBigInteger('order_item_unit_id')->default(0);
            $table->string('type')->comment('调整的类型：运费、促销等等');
            $table->string('label')->comment('结合 type 决定；例如：10元代金券、运费');
            $table->string('origin_code')->default('')->comment('结合 label 决定；例如：代金券的code');
            $table->boolean('is_included')->comment('判断本条调整记录是否会影响最终订单需要支付的价格');
            $table->decimal('amount', 10, 2)->comment('调整的价格。负值为优惠，正值为加价');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_adjustments');
    }
}
