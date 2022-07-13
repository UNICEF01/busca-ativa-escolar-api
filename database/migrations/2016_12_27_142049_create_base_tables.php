<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaseTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

	    Schema::table("users", function (Blueprint $table) {
		    $table->string('type')->index();
		    $table->uuid('tenant_id')->index()->nullable();
		    $table->uuid('city_id')->index()->nullable();

		    $table->softDeletes();
	    });

        Schema::create("tenants", function (Blueprint $table) {
	        $table->uuid('id');
	        $table->primary('id');

	        $table->uuid('city_id');
	        $table->uuid('operational_admin_id');
	        $table->uuid('political_admin_id');

	        $table->boolean('is_registered')->index()->default(false);
	        $table->boolean('is_active')->index()->default(false);

	        $table->dateTime('last_active_at');
	        $table->dateTime('registered_at');
	        $table->dateTime('activated_at');

	        $table->timestamps();
	        $table->softDeletes();

        });

	    Schema::create("cities", function (Blueprint $table) {
		    $table->uuid('id');
		    $table->primary('id');

		    $table->string('region', 2)->index();
		    $table->string('uf', 2)->index();

		    $table->string('slug')->index()->nullable();

		    $table->string('name')->nullable();

		    $table->string('ibge_city_id')->index()->nullable();
		    $table->string('ibge_uf_id')->nullable();
		    $table->string('ibge_region_id')->nullable();

		    $table->string('webdoc_url')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
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
