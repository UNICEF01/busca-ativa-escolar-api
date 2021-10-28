<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapCountry extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_country', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string("place_uf");
            $table->integer("value");
            $table->string("idMap");
            $table->string("displayValue");
            $table->integer("showLabel");
            $table->string("simple_name");
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
        Schema::dropIfExists('map_country');
    }
}
