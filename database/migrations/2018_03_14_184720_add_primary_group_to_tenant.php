<?php

use BuscaAtivaEscolar\Group;
use BuscaAtivaEscolar\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimaryGroupToTenant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("tenants", function (Blueprint $table) {
        	$table->uuid('primary_group_id')->after('signup_id')->nullable();
        });

        Tenant::all()->each(function ($tenant) { /* @var $tenant Tenant */
            $group = Group::query()
	            ->where('tenant_id', $tenant->id)
	            ->where('is_primary', true)
	            ->first();

            if(!$group) return;

        	$tenant->primary_group_id = $group->id;
        	$tenant->save();
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
