<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductAttributeTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attribute_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 64)->comment('商品规格名称')->unique();
            $table->json('attributes')->comment('商品规格属性');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        DB::statement("ALTER TABLE `product_attribute_templates` COMMENT='商品规格模板'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_attribute_templates');
    }
}
