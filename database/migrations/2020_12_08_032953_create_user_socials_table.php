<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSocialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_socials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('用户 id');
            $table->string('social_type', 32)->comment('第三方平台');
            $table->string('openid')->comment('第三方平台标识');
            $table->string('unionid')->default('')->comment('第三方平台关联标识');
            $table->json('extra')->nullable()->comment('其他额外数据');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');

            $table->unique(['social_type', 'openid']);
            $table->unique(['social_type', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_socials');
    }
}
