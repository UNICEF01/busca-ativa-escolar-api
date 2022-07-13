<?php

use BuscaAtivaEscolar\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class AddNameAsciiToTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("tenants", function (Blueprint $table) {
        	$table->string('name_ascii')->after('name')->nullable()->index();
        });

        foreach(Tenant::all() as $tenant) {
		    $tenant->name_ascii = strtolower(Str::ascii($tenant->name));
		    $tenant->save();
	    }
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
