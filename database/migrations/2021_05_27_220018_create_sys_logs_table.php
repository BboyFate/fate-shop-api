<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source_type', 32)->comment('来源类型');
            $table->ipAddress('ip_address')->comment('用户IP ip2long');
            $table->json('extra')->comment('数据');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_logs');
    }
}
