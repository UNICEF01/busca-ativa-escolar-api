<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNullOnTenantAdminIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	DB::statement("ALTER TABLE tenants MODIFY political_admin_id CHAR(36) NULL");
    	DB::statement("ALTER TABLE tenants MODIFY operational_admin_id CHAR(36) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
