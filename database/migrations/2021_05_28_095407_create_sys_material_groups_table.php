<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysMaterialGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_material_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 128)->unique()->comment('素材分组名');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedInteger('level')->comment('当前分组层级');
            $table->string('level_path', 128)->comment('当前分组层级标识，方便搜索');
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
        Schema::dropIfExists('sys_material_groups');
    }
}
