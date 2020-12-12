<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminRoleHasMenus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_role_has_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('menu_id')
                ->references('id')
                ->on('admin_vue_menus')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on(config('permission.table_names.roles'))
                ->onDelete('cascade');

            $table->primary(['menu_id', 'role_id'], 'role_has_menus_menu_id_role_id_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_role_has_menus');
    }
}
