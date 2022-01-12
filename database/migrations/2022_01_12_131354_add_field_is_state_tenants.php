<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldIsStateTenants extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenats', function($table) {
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
        Schema::table('tenats', function($table) {
            $table->dropColumn('is_state');
        });
    }
}
