<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableCaseStepsPesquisaAddColumnsPlaceIndigenaCampoRibeirinha extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('case_steps_pesquisa', function (Blueprint $table) {
            $table->boolean('place_is_indigena')->index()->nullable();
            $table->boolean('place_is_do_campo')->index()->nullable();
            $table->boolean('place_is_ribeirinha')->index()->nullable();
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
