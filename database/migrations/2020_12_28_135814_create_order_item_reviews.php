<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->comment('评论的用户 ID');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sku_id');
            $table->unsignedTinyInteger('rating')->comment('用户打分');
            $table->text('review')->comment('用户评价内容');
            $table->json('images')->nullable()->comment('评论图片');
            $table->boolean('is_verified')->default(false)->comment('是否审核通过');
            $table->dateTime('reviewed_at')->default('1000-01-01 00:00:00')->comment('用户评论时间');
            $table->dateTime('verified_at')->default('1000-01-01 00:00:00')->comment('审核评论时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_item_reviews');
    }
}
