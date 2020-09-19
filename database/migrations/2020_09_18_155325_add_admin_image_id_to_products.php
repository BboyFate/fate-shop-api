<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdminImageIdToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image']);

            $table->unsignedBigInteger('admin_image_id')->comment('商品封面图片')->after('category_id');
            $table->foreign('admin_image_id')->references('id')->on('admin_images')->onDelete('cascade');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['admin_image_id']);
            $table->dropColumn(['admin_image_id']);

            $table->string('image')->comment('商品封面图片文件路径')->after('description');
        });
    }
}
