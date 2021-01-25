<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRefundCausesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refund_causes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('原因');
            $table->unsignedTinyInteger('sorted')->default(0)->comment('排序, 数值小的靠前');
            $table->boolean('is_showed')->default(true)->comment('是否显示该原因');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
        Schema::dropIfExists('order_refund_causes');
    }
}
