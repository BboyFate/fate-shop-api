<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('类目名');
            $table->string('image')->default('')->comment('类目图片');
            $table->unsignedBigInteger('parent_id')->default(0)->index();
            $table->unsignedInteger('level')->comment('当前类目层级');
            $table->string('path')->comment('该类目的父类目ID，方便搜索');
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
        Schema::dropIfExists('product_categories');
    }
}
