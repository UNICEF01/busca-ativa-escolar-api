<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUfFieldsToStateAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
        	$table->string('uf', 2)->index()->after('city_id')->nullable();
        });

        Schema::table("tenants", function (Blueprint $table) {
        	$table->string('uf', 2)->index()->after('city_id')->nullable();
        });

        $tenants = \BuscaAtivaEscolar\Tenant::all();

        foreach($tenants as $tenant) {
        	if(!$tenant || !$tenant->city) continue;
        	$tenant->update(['uf' => $tenant->city->uf]);
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
