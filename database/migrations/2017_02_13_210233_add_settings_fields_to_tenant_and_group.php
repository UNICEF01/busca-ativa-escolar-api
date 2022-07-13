<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingsFieldsToTenantAndGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("groups", function (Blueprint $table) {
        	$table->longText('settings')->nullable();
        });

	    Schema::table("tenants", function (Blueprint $table) {
		    $table->longText('settings')->nullable();
	    });
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
