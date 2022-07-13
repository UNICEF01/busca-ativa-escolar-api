<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartedAtFieldsToSteps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    $steps = ['alerta', 'analise_tecnica', 'gestao_do_caso', 'observacao', 'pesquisa', 'rematricula'];

	    foreach($steps as $step) {
		    Schema::table("case_steps_{$step}", function (Blueprint $table) {
			    $table->dateTime('started_at')->before('completed_at')->nullable();
		    });
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
