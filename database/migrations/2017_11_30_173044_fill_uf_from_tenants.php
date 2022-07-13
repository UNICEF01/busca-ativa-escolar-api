<?php

use BuscaAtivaEscolar\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillUfFromTenants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tenants = Tenant::with('city')->get();

        foreach($tenants as $tenant) {
        	DB::table("tenants")->where('id', $tenant->id)->update(['uf' => $tenant->city->uf]);
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
