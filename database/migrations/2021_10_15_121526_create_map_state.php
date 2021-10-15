<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapState extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('map_state', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('uf');
            $table->integer('idMap');
            $table->integer('value');
            $table->string('name_city');
            $table->integer('showLabel');
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
        Schema::dropIfExists('map_state');
    }
}
