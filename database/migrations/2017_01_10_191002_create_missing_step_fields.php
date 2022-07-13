<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissingStepFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

    	function addNewStepFields(Blueprint $table) {
		    $table->integer('step_index')->after('step_type')->index()->default(1);
		    $table->integer('next_index')->after('step_index')->index()->nullable();
		    $table->string('next_type')->after('next_index')->index()->nullable();
		    $table->datetime('completed_at')->after('next_type')->index()->nullable();
	    }

        Schema::table("case_steps_pesquisa", function (Blueprint $table) {
	        addNewStepFields($table);

        	$table->string('reason_not_enrolled')->after('has_been_in_school')->nullable();
        	$table->string('school_last_name')->after('school_last_year')->nullable();
        	$table->string('work_activity_other')->after('work_activity')->nullable();
        	$table->boolean('handicapped_at_sus')->after('case_cause_ids')->index()->nullable();
        	$table->string('handicapped_reason_not_enrolled')->after('case_cause_ids')->nullable();
        });

        Schema::table("case_steps_observacao", function (Blueprint $table) {
	        addNewStepFields($table);

        	$table->string('evasion_reason')->after('is_child_still_in_school')->nullable();
        });

        Schema::create("case_steps_analise_tecnica", function (Blueprint $table) {
	        $table->uuid('id');
	        $table->primary('id');

	        $table->boolean('is_completed')->index()->default(false);

	        $table->uuid('child_id')->index();
	        $table->uuid('case_id')->index();
	        $table->string('step_type')->index();

	        $table->integer('step_index')->index()->default(1);
	        $table->integer('next_index')->index()->nullable();
	        $table->string('next_type')->index()->nullable();
	        $table->datetime('completed_at')->index()->nullable();

	        $table->mediumText('analysis_details')->nullable();

	        $table->timestamps();
	        $table->softDeletes();
        });

        Schema::table("case_steps_gestao_do_caso", function (Blueprint $table) {
	        addNewStepFields($table);
        });

	    Schema::table("case_steps_rematricula", function (Blueprint $table) {
		    addNewStepFields($table);
	    });

	    Schema::table("case_steps_alerta", function (Blueprint $table) {
		    addNewStepFields($table);
	    });

	    Schema::table("children_cases", function (Blueprint $table) {
		    $table->datetime('enrolled_at')->after('updated_at')->index()->nullable();
		    $table->string('alert_cause_id')->after('created_by_user_id')->index()->nullable();
		    $table->json('case_cause_ids')->after('created_by_user_id')->nullable();
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
