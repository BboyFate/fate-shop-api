<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')->nullable()->comment('素材分组 ID');
            $table->string('name', 128)->comment('素材名');
            $table->unsignedBigInteger('size')->comment('素材大小，单位字节');
            $table->string('type', 32)->comment('素材类型');
            $table->string('mime', 32)->comment('素材扩展类型');
            $table->string('path', 510)->comment('素材路径');
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
        Schema::dropIfExists('sys_materials');
    }
}
