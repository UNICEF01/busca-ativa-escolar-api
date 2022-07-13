<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCoordsFieldToSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("case_steps_alerta", function (Blueprint $table) {
        	$table->decimal("place_lat", 18, 12)->nullable();
        	$table->decimal("place_lng", 18, 12)->nullable();
        	$table->string("place_map_region")->nullable();
        	$table->json("place_map_geocoded_address")->nullable();
        });

	    Schema::table("case_steps_pesquisa", function (Blueprint $table) {
		    $table->decimal("place_lat", 18, 12)->nullable();
		    $table->decimal("place_lng", 18, 12)->nullable();
        	$table->string("place_map_region")->nullable();
		    $table->json("place_map_geocoded_address")->nullable();
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
