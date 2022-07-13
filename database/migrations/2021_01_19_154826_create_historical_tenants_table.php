<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoricalTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //add a new column to historical_signups
        Schema::table('historical_signups', function($table) {
            $table->string('judged_by');
        });

        Schema::create('historical_tenants', function (Blueprint $table) {

            $table->uuid('id');
            $table->primary('id');

            $table->string('uf', 2);
            $table->uuid('city_id');
            $table->uuid('operational_admin_id');
            $table->uuid('political_admin_id');
            $table->uuid('signup_id');
            $table->uuid('primary_group_id');

            $table->boolean('is_registered')->index()->default(false);
            $table->boolean('is_active')->index()->default(false);
            $table->boolean('is_setup')->index()->default(false);

            $table->dateTime('last_active_at');
            $table->dateTime('registered_at');
            $table->dateTime('activated_at');

            $table->timestamps();
            $table->softDeletes();

            $table->string('name', 255);
            $table->string('name_ascii', 255);
            $table->longText('settings');

            $table->decimal('map_lat', 18,12);
            $table->decimal('map_lng', 18,12);

            $table->longText('educacenso_import_details');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historical_tenants');
    }
}
