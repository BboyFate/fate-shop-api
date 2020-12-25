<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32)->comment('名称');
            $table->string('type', 32)->comment('轮播类型');
            $table->string('url')->default('')->comment('轮播地址');
            $table->unsignedTinyInteger('sorted')->default(0)->comment('排序');
            $table->boolean('is_showed')->default(true)->comment('显示隐藏');
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
        Schema::dropIfExists('banners');
    }
}
