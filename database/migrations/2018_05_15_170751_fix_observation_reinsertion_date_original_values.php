<?php

use BuscaAtivaEscolar\CaseSteps\CaseStep;
use BuscaAtivaEscolar\CaseSteps\Observacao;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixObservationReinsertionDateOriginalValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

    	$observacoes = Observacao::query()
		    ->with(['child', 'childCase'])
		    ->get();

    	foreach($observacoes as $obs) {

    		if($obs->childCase->current_step_type !== "BuscaAtivaEscolar\\CaseSteps\\Observacao") continue;

    		$rematricula = CaseStep::fetchWithinCase($obs->case_id, "BuscaAtivaEscolar\\CaseSteps\\Rematricula", 50);

    		if(!$obs->reinsertion_date_original) {
    			$obs->reinsertion_date_original = $rematricula->reinsertion_date;
		    }

		    if(!$obs->reinsertion_date) {
    			$obs->reinsertion_date = $rematricula->reinsertion_date;
		    }

		    $obs->save();
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
