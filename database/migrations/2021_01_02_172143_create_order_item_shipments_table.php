<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('express_company_id');

            $table->string('shipment_state')->default(\App\Models\OrderItemShipment::SHIPMENT_STATE_PENDING)->comment('运输状态');
            $table->string('express_no')->nullable()->comment('物流单号');
            $table->json('express_data')->nullable()->comment('物流数据');

            $table->dateTime('readied_at')->nullable()->comment('备货时间');
            $table->dateTime('delivered_at')->nullable()->comment('发货时间');
            $table->dateTime('received_at')->nullable()->comment('收货时间');
            $table->dateTime('canceled_at')->nullable()->comment('退货时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_shipments');
    }
}
