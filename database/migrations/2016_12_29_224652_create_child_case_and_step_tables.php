<?php

use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\ChildCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChildCaseAndStepTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("children", function (Blueprint $table) {
	        $table->uuid('id');
	        $table->primary('id');

	        $table->uuid('tenant_id')->index();
	        $table->uuid('city_id')->index();

	        $table->string('child_status')->index()->default(Child::STATUS_OUT_OF_SCHOOL);

	        $table->string('name')->nullable();
	        $table->string('mother_name')->nullable();
	        $table->string('father_name')->nullable();
	        $table->integer('age')->nullable();

	        $table->uuid('current_case_id')->index()->nullable();
	        $table->string('current_step_type')->index()->nullable();
	        $table->uuid('current_step_id')->index()->nullable();

	        $table->timestamps();
	        $table->softDeletes();
        });

	    Schema::create("children_cases", function (Blueprint $table) {
		    $table->uuid('id');
		    $table->primary('id');

		    $table->uuid('child_id')->index();

		    $table->string('case_status')->index()->default(ChildCase::STATUS_IN_PROGRESS);

		    $table->string('name')->nullable();
		    $table->string('risk_level')->default(ChildCase::RISK_LEVEL_MEDIUM);

		    $table->boolean('is_current')->index()->default(true);

		    $table->uuid('assigned_group_id')->index()->nullable();
		    $table->uuid('assigned_user_id')->index()->nullable();
		    $table->uuid('created_by_user_id')->index()->nullable();

		    $table->uuid('current_step_id')->nullable();
		    $table->string('current_step_type')->nullable();

		    $table->json('linked_steps')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
	    });

	    function buildCaseStep(Blueprint $table) {
		    $table->uuid('id');
		    $table->primary('id');

		    $table->boolean('is_completed')->index()->default(false);

		    $table->uuid('child_id')->index();
		    $table->uuid('case_id')->index();
		    $table->string('step_type')->index();
	    }

	    Schema::create("case_steps_alerta", function (Blueprint $table) {
		    buildCaseStep($table);

		    $table->string('name')->nullable();
		    $table->string('gender')->index()->nullable();
		    $table->string('race')->index()->nullable();
		    $table->date('dob')->nullable();

		    $table->string('rg')->nullable();
		    $table->string('cpf')->nullable();
		    $table->string('nis')->nullable();

		    $table->integer('alert_cause_id')->index()->nullable();

		    $table->string('mother_name')->nullable();
		    $table->string('mother_rg')->nullable();
		    $table->string('mother_phone')->nullable();
		    $table->string('father_name')->nullable();
		    $table->string('father_rg')->nullable();
		    $table->string('father_phone')->nullable();

		    $table->string('place_address')->nullable();
		    $table->string('place_cep')->index()->nullable();
		    $table->string('place_reference')->nullable();
		    $table->string('place_neighborhood')->nullable();
		    $table->string('place_city')->index()->nullable();
		    $table->string('place_uf')->index()->nullable();
		    $table->string('place_phone')->nullable();
		    $table->string('place_mobile')->nullable();

		    $table->timestamps();
		    $table->softDeletes();

	    });

	    Schema::create("case_steps_pesquisa", function (Blueprint $table) {
		    buildCaseStep($table);

		    $table->string('name')->nullable();
		    $table->string('gender')->index()->nullable();
		    $table->string('race')->index()->nullable();
		    $table->date('dob')->nullable();
		    $table->string('rg')->nullable();
		    $table->string('cpf')->nullable();

		    $table->boolean('has_been_in_school')->index()->nullable();
		    $table->string('school_last_grade')->nullable();
		    $table->string('school_last_year')->nullable();
		    $table->string('school_last_status')->nullable();
		    $table->string('school_last_age')->nullable();
		    $table->string('school_last_address')->nullable();

		    $table->boolean('is_working')->index()->nullable();
		    $table->string('work_activity')->nullable();
		    $table->string('work_is_paid')->nullable();
		    $table->string('work_weekly_hours')->nullable();

		    $table->boolean('parents_has_mother')->index()->nullable();
		    $table->boolean('parents_has_father')->index()->nullable();
		    $table->boolean('parents_has_brother')->index()->nullable();

		    $table->string('parents_who_is_guardian')->index()->nullable();
		    $table->string('parents_income')->nullable();
		    $table->string('mother_name')->nullable();

		    $table->string('guardian_name')->nullable();
		    $table->string('guardian_rg')->nullable();
		    $table->string('guardian_cpf')->nullable();
		    $table->string('guardian_dob')->nullable();
		    $table->string('guardian_phone')->nullable();
		    $table->string('guardian_race')->index()->nullable();
		    $table->string('guardian_schooling')->index()->nullable();
		    $table->string('guardian_job')->nullable();

		    $table->json('case_cause_ids')->nullable();

		    $table->string('place_address')->nullable();
		    $table->string('place_cep')->nullable();
		    $table->string('place_reference')->nullable();
		    $table->string('place_neighborhood')->nullable();
		    $table->string('place_city')->index()->nullable();
		    $table->string('place_uf')->index()->nullable();
		    $table->string('place_kind')->index()->nullable();
		    $table->boolean('place_is_quilombola')->index()->nullable();

		    $table->timestamps();
		    $table->softDeletes();
	    });

	    Schema::create("case_steps_gestao_do_caso", function (Blueprint $table) {
		    buildCaseStep($table);

		    $table->json('identified_cause_ids')->nullable();
		    $table->string('risk_level')->index()->nullable();
		    $table->mediumText('actions_description')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
	    });

	    Schema::create("case_steps_rematricula", function (Blueprint $table) {
		    buildCaseStep($table);

		    $table->date('reinsertion_date')->nullable();
			$table->string('reinsertion_grade')->nullable();

			$table->string('school_name')->nullable();
			$table->string('school_censo_id')->nullable();
			$table->string('school_address')->nullable();
			$table->string('school_cep')->nullable();
			$table->string('school_neighborhood')->nullable();
			$table->string('school_city')->nullable();
			$table->string('school_uf')->nullable();
			$table->string('school_contact_name')->nullable();
			$table->string('school_contact_email')->nullable();
			$table->string('school_contact_position')->nullable();
			$table->string('school_phone')->nullable();
			$table->string('school_email')->nullable();

			$table->mediumText('observations')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
	    });


	    Schema::create("case_steps_observacao", function (Blueprint $table) {
		    buildCaseStep($table);

		    $table->date('report_date')->nullable();
		    $table->integer('report_index')->index()->nullable();

		    $table->boolean('is_child_still_in_school')->index()->nullable();

		    $table->mediumText('observations')->nullable();

		    $table->timestamps();
		    $table->softDeletes();
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop("children");
        Schema::drop("children_cases");
        Schema::drop("case_steps_alerta");
        Schema::drop("case_steps_pesquisa");
        Schema::drop("case_steps_gestao_do_caso");
        Schema::drop("case_steps_rematricula");
        Schema::drop("case_steps_observacao");
    }
}
