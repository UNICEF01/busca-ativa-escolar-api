<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateCaseStepsPesquisaAddHasGrandparents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('case_steps_pesquisa', function ($table){
            $table->boolean('parents_has_grandparents')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::table('case_steps_pesquisa', function($table) {
            $table->dropColumn('parents_has_grandparents');
        });
    }
}
