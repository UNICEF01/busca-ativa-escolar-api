<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssignmentFieldsToStepsAndCases extends Migration
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
        		$table->uuid('assigned_user_id')->after('next_type')->index()->nullable();
        		$table->uuid('assigned_group_id')->after('assigned_user_id')->index()->nullable();
        		$table->boolean('is_pending_assignment')->after('assigned_group_id')->index()->default(0);
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
