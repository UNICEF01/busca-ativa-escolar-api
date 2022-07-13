<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIsStateTenantsSignup extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenant_signups', function($table) {
            $table->boolean('is_state')->index()->default(false)->comment = 'flag para ver se é uma cidade-estado';
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
            $table->dropColumn('is_state');
        });
    }
}
