<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeocodingToChildren extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("children", function (Blueprint $table) {
        	$table->decimal("lat", 18, 12)->nullable();
        	$table->decimal("lng", 18, 12)->nullable();
        	$table->string("map_region")->index()->nullable();
        	$table->json("map_geocoded_address")->nullable();
        });

        Schema::table("tenants", function (Blueprint $table) {
        	$table->decimal("map_lat", 18, 12)->nullable();
        	$table->decimal("map_lng", 18, 12)->nullable();
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
