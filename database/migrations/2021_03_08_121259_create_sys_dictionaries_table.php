<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysDictionariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_dictionaries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dictionary_type_id');
            $table->string('lavel', 64)->comment('字典标签');
            $table->string('value', 64)->comment('字典键值');
            $table->string('value_type', 32)->comment('字典键值的类型: booelan、numeric、integer 等');
            $table->boolean('is_disabled')->default(false)->comment('是否停用');
            $table->boolean('is_default')->default(false)->comment('是否默认');
            $table->unsignedTinyInteger('sorted')->default(0)->comment('排序');
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
        Schema::dropIfExists('sys_dictionaries');
    }
}
