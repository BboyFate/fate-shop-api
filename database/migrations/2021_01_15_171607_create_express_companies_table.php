<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpressCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('express_companies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->comment('物流公司名称');
            $table->unsignedTinyInteger('sorted')->default(0)->comment('排序, 数值大的靠前');
            $table->boolean('is_default')->default(false)->comment('默认选择的物流');
            $table->boolean('is_showed')->default(true)->comment('是否开启');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('express_companies');
    }
}
