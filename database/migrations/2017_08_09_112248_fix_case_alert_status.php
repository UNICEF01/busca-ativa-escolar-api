<?php

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCaseAlertStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    $rejectedChildren = Child::query()->rejected()->get(['id'])->pluck('id')->toArray();

	    DB::table("children_cases")
		    ->whereIn('child_id', $rejectedChildren)
		    ->update([
		    	'case_status' => ChildCase::STATUS_CANCELLED,
			    'cancel_reason' => ChildCase::CANCEL_REASON_REJECTED_ALERT
		    ]);

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
