<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewCityFieldsToSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `case_steps_alerta` CHANGE `place_city` `place_city_name` VARCHAR(255)');
        DB::statement('ALTER TABLE `case_steps_pesquisa` CHANGE `place_city` `place_city_name` VARCHAR(255)');
        DB::statement('ALTER TABLE `case_steps_rematricula` CHANGE `school_city` `school_city_name` VARCHAR(255)');

        Schema::table("case_steps_alerta", function (Blueprint $table) {
        	$table->uuid('place_city_id')->index()->nullable()->before('place_uf');
        });

	    Schema::table("case_steps_pesquisa", function (Blueprint $table) {
		    $table->uuid('place_city_id')->index()->nullable()->before('place_uf');
	    });

	    Schema::table("case_steps_rematricula", function (Blueprint $table) {
		    $table->uuid('school_city_id')->index()->nullable()->before('school_uf');
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
