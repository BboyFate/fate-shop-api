<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminVueMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_vue_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('admin_vue_menus')->onDelete('cascade');
            $table->string('name')->comment('router 标识名称');
            $table->string('path')->comment('router 的路径');
            $table->string('redirect')->nullable()->comment('router 重定向');
            $table->string('component');
            $table->json('meta')->nullable()->comment('router 元信息');
            $table->unsignedTinyInteger('level')->comment('层级，0 表示最顶层');
            $table->string('str_ids')->comment('存储父子 ID 层级，方便筛选');
            $table->unsignedTinyInteger('sorted')->default(0)->comment('菜单排序, 数值大的靠前');
            $table->boolean('is_showed')->default(true)->comment('默认显示');
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
        Schema::dropIfExists('admin_vue_menus');
    }
}
