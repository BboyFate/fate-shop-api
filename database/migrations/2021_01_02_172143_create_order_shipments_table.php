<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('express_company_id');
            $table->string('shipment_state')->default(\App\Models\Orders\OrderShipment::SHIPMENT_STATE_PENDING)->comment('运输状态');
            $table->string('express_no')->nullable()->comment('物流单号');
            $table->json('extra')->nullable()->comment('扩展数据');
            $table->dateTime('delivered_at')->nullable()->comment('发货时间');
            $table->dateTime('received_at')->nullable()->comment('收货时间');
            $table->dateTime('refunded_at')->nullable()->comment('退货时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_shipments');
    }
}
