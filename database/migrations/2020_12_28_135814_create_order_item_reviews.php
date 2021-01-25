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
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_item_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_sku_id');

            $table->float('rating',3, 2)->comment('用户打分');
            $table->text('review')->comment('用户评价内容');
            $table->boolean('is_verified')->default(false)->comment('是否审核通过');

            $table->dateTime('created_at')->comment('用户评论时间');
            $table->dateTime('verified_at')->nullable()->comment('审核评论时间');
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
