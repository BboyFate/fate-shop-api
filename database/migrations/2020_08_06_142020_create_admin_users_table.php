<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username');
            $table->string('password')->nullable();
            $table->string('nickname');
            $table->char('phone', 11)->unique()->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_enabled')->default(true)->comment('默认允许登录');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('last_actived_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users');
    }
}
