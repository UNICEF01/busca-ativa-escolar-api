<?php

use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixAcceptedAlerts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $acceptedChildren = Child::query()->accepted()->get(['id'])->pluck('id')->toArray();
	    $rejectedChildren = Child::query()->rejected()->get(['id'])->pluck('id')->toArray();

        DB::table("case_steps_alerta")
	        ->whereIn('child_id', $acceptedChildren)
	        ->update(['alert_status' => 'accepted']);

	    DB::table("case_steps_alerta")
		    ->whereIn('child_id', $rejectedChildren)
		    ->update(['alert_status' => 'rejected']);

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
