<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 32)->default(\App\Models\Products\Product::TYPE_NORMAL)->index();
            $table->unsignedBigInteger('category_id')->index()->comment('商品类目 ID');
            $table->unsignedBigInteger('express_fee_id')->index()->comment('运费模板 ID');
            $table->string('title')->comment('商品短标题');
            $table->string('long_title')->comment('商品长标题');
            $table->string('number', 64)->default('')->comment('商品货号');
            $table->string('image')->comment('商品封面图片');
            $table->json('banners')->comment('商品轮播图');
            $table->boolean('on_sale')->default(true)->comment('商品是否正在售卖');
            $table->boolean('is_free_shipping')->default(false)->comment('是否免邮费');
            $table->float('rating', 3, 2)->default(5)->comment('商品平均评分');
            $table->unsignedInteger('sold_count')->default(0)->comment('销量');
            $table->unsignedInteger('review_count')->default(0)->comment('评论数量');
            $table->decimal('price', 10, 2)->default(0)->comment('SKU 最低价格');
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
        Schema::dropIfExists('products');
    }
}
