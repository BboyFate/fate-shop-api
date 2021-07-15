<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSysUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('phone', 11)->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('nickname');
            $table->string('avatar')->nullable();
            $table->boolean('is_disabled')->default(true)->comment('默认允许登录');
            $table->dateTime('last_actived_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_users');
    }
}
