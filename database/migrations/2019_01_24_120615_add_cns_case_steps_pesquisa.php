<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCnsCaseStepsPesquisa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Schema::table('case_steps_pesquisa', function ($table){
            $table->string('cns', 15)->after('cpf')->nullable();
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
            $table->dropColumn('cns');
        });
    }
}
