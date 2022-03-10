<?php
/**
 * busca-ativa-escolar-api
 * PendingAlertTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 09/02/2017, 20:21
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\CaseSteps\Alerta;
use BuscaAtivaEscolar\Child;
use BuscaAtivaEscolar\Data\AlertCause;
use League\Fractal\TransformerAbstract;
use phpDocumentor\Reflection\Types\Null_;

class PendingAlertTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'alert',
		'submitter',
	];

	protected $defaultIncludes = [
		'alert',
		'submitter',
	];

	public function transform(Child $child) {


		$nameOfGroup = null;
		if ($child->currentCase() != null){
			if ($child->currentCase->group != null){
				$nameOfGroup = $child->currentCase->group->name;
				$idGroup = $child->currentCase->group->id;
			}
		}

	

		return [
			'id' => $child->id,
			'name' => $child->name,
			'group_name' => $nameOfGroup,
			'group_id' => $idGroup,

			'tenant_id' => $child->tenant_id,

			'mother_name' => $child->mother_name,
			'father_name' => $child->father_name,

			'risk_level' => $child->risk_level,
			'gender' => $child->gender,
			'age' => $child->age,

			'current_case_id' => $child->current_case_id,
			'alert_submitter_id' => $child->alert_submitter_id,

			'current_step_type' => $child->current_step_type,
			'current_step_id' => $child->current_step_id,

			'child_status' => $child->child_status,
			'alert_status' => $child->alert_status,

			'requires_address_update' => ( ($child->alert && $child->alert->place_neighborhood == null && $child->alert->place_address == null) OR
                ($child->alert && $child->alert->place_neighborhood == "" && $child->alert->place_address == "")),

			'created_at' => $child->created_at ? $child->created_at->toIso8601String() : null,
			'updated_at' => $child->updated_at ? $child->updated_at->toIso8601String() : null,
		];
	}

	public function includeAlert(Child $child) {
		if(!$child->alert) return null;
		return $this->item($child->alert, new StepFieldsTransformer(), false);
	}

	public function includeSubmitter(Child $child) {
		if(!$child->submitter) return null;
		return $this->item($child->submitter, new UserTransformer(), false);
	}

}