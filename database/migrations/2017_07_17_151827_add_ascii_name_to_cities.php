<?php

use BuscaAtivaEscolar\City;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

class AddAsciiNameToCities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("cities", function (Blueprint $table) {
        	$table->string('name_ascii')->after('name')->nullable();
        });

        $cities = City::all();

        foreach($cities as $city) {
        	$city->update(['name_ascii' => Str::ascii($city->name)]);
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
