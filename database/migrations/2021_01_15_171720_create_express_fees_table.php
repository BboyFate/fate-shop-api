<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Expresses\ExpressFee;

class CreateExpressFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('express_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('express_company_id');
            $table->string('name', 64)->comment('运费名称');
            $table->string('fee_type', 32)->default(ExpressFee::FEE_TYPE_WEIGHT)->comment('计运费：默认按重量');
            $table->boolean('is_default')->default(false)->comment('默认选择的运费模板');
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
        Schema::dropIfExists('express_fees');
    }
}
