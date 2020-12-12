<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 1000);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('system_image_categories');
            $table->string('mime', 40)->comment('图片扩展类型');
            $table->string('path')->comment('图片路径');
            $table->string('size', 30)->comment('图片大小，单位字节');
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
        Schema::dropIfExists('system_images');
    }
}
