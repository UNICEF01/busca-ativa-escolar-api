<?php

use Sleimanx2\Plastic\Map\Blueprint;
use Sleimanx2\Plastic\Mappings\Mapping;

/**
 * Class BuscaAtivaEscolarChild
 * @deprecated
 */
class BuscaAtivaEscolarChild extends Mapping
{
    /**
     * Full name of the model that should be mapped
     *
     * @var string
     */
    protected $model = BuscaAtivaEscolar\Child::class;

    /**
     * Run the mapping.
     *
     * @return void
     */
    public function map()
    {

        Map::create($this->getModelType(),function(Blueprint $map){

	        $map->string("id")->store('true');
	        $map->string("tenant_id")->store('true');
	        $map->string("city_id")->store('true');
	        $map->string("child_status")->store('true');
	        $map->string("name")->store('true')->index('analyzed')->analyzer('folding');
	        $map->string("mother_name")->store('true')->index('analyzed')->analyzer('folding');
	        $map->string("father_name")->store('true')->index('analyzed')->analyzer('folding');
	        $map->integer("age")->store('true');
	        $map->string("gender")->store('true');
	        $map->string("risk_level")->store('true');
	        $map->string("current_case_id")->store('true');
	        $map->date("created_at")->store('true')->format('YYYY-MM-dd HH:mm:ss');
	        $map->date("updated_at")->store('true')->format('YYYY-MM-dd HH:mm:ss');
	        $map->date("deleted_at")->store('true')->format('YYYY-MM-dd HH:mm:ss');

	        $map->object('current_case', function(Blueprint $map) {
		        $map->string("case_status")->store('true');
		        $map->string("name")->store('true')->index('analyzed')->analyzer('folding');
		        $map->string("risk_level")->store('true');
		        $map->string("assigned_group_id")->store('true');
		        $map->string("assigned_user_id")->store('true');
		        $map->string("created_by_user_id")->store('true');
		        $map->string("case_cause_ids")->store('true');
		        $map->string("alert_cause_id")->store('true');
		        $map->string("current_step_id")->store('true');
		        $map->string("current_step_type")->store('true');
		        $map->date("enrolled_at")->store('true')->format('dateOptionalTime');
		        $map->string("cancel_reason")->store('true');
	        });
	        $map->string("race")->store('true');
	        $map->date("dob")->store('true')->format('dateOptionalTime');
	        $map->string("rg");
	        $map->string("cpf");
	        $map->string("nis");
	        $map->string("alert_cause_id");
	        $map->string("place_address")->index('analyzed')->analyzer('folding');
	        $map->string("place_cep");
	        $map->string("place_reference")->index('analyzed')->analyzer('folding');
	        $map->string("place_neighborhood")->index('analyzed')->analyzer('folding');
	        $map->string("place_city");
	        $map->string("place_uf");
	        $map->boolean("has_been_in_school");
	        $map->string("reason_not_enrolled");
	        $map->string("school_last_grade");
	        $map->integer("school_last_year");
	        $map->string("school_last_name")->index('analyzed')->analyzer('folding');
	        $map->string("school_last_status");
	        $map->integer("school_last_age");
	        $map->string("school_last_address")->index('analyzed')->analyzer('folding');
	        $map->boolean("is_working");
	        $map->string("work_activity");
	        $map->string("work_activity_other")->index('analyzed')->analyzer('folding');
	        $map->boolean("work_is_paid");
	        $map->integer("work_weekly_hours");
	        $map->boolean("parents_has_mother");
	        $map->boolean("parents_has_father");
	        $map->boolean("parents_has_brother");
            $map->boolean("parents_has_grandparents");
	        $map->string("parents_who_is_guardian");
	        $map->string("parents_income");
	        $map->string("guardian_name")->index('analyzed')->analyzer('folding');
	        $map->date("guardian_dob")->format('dateOptionalTime');
	        $map->string("guardian_race");
	        $map->string("guardian_schooling");
	        $map->string("guardian_job");
	        $map->string("case_cause_ids");
	        $map->string("handicapped_reason_not_enrolled");
	        $map->boolean("handicapped_at_sus");
	        $map->string("place_kind");
	        $map->string("place_is_quilombola");
	        $map->string("actions_description");
	        $map->date("reinsertion_date")->format('dateOptionalTime');
	        $map->string("reinsertion_grade");
	        $map->string("school_name")->index('analyzed')->analyzer('folding');
	        $map->string("school_censo_id");
	        $map->string("school_address")->index('analyzed')->analyzer('folding');
	        $map->string("school_cep");
	        $map->string("school_neighborhood")->index('analyzed')->analyzer('folding');
	        $map->string("school_city")->index('analyzed')->analyzer('folding');
	        $map->string("school_uf")->index('analyzed')->analyzer('folding');
	        $map->string("school_contact_name")->index('analyzed')->analyzer('folding');
	        $map->string("observations")->index('analyzed')->analyzer('folding');
	        $map->boolean("is_child_still_in_school");
	        $map->string("evasion_reason")->index('analyzed')->analyzer('folding');
	        $map->string("city")->store('true');
	        $map->string("uf")->store('true');
	        $map->string("country_region")->store('true');

        },$this->getModelIndex());
    }
}
