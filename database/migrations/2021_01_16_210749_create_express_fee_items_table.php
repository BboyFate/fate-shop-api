<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpressFeeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('express_fee_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('express_fee_id');
            $table->json('provinces')->comment('邮费包含的省份');
            $table->json('fees')->comment('运费：包含首重续重、体积');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('express_fee_items');
    }
}
