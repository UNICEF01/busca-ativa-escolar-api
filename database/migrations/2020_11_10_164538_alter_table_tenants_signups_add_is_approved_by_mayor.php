<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTenantsSignupsAddIsApprovedByMayor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_signups', function($table) {
            $table->boolean('is_approved_by_mayor')->after('is_provisioned')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tenant_signups', function($table) {
            $table->dropColumn('is_approved_by_mayor');
        });
    }
}
