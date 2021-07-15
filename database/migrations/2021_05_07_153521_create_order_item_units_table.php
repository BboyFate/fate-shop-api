<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_item_id')->index();
            $table->unsignedBigInteger('order_shipment_id')->default(0)->comment('物流运输 外键')->index();
            $table->unsignedBigInteger('order_item_refund_id')->default(0)->comment('退货退款 外键')->index();
            $table->decimal('payment_price', 10, 2)->comment('每个单位实际支付金额');
            $table->decimal('adjustment_total', 10, 2)->default(0)->comment('调整金额');
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
        Schema::dropIfExists('order_item_units');
    }
}
