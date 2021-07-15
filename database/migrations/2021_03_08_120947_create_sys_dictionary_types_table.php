<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysDictionaryTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_dictionary_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->comment('字典名称');
            $table->string('type', 64)->comment('字典类型')->unique();
            $table->boolean('is_disabled')->default(false)->comment('是否停用');
            $table->string('remark', 128)->nullable()->comment('备注');
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
        Schema::dropIfExists('sys_dictionary_types');
    }
}
