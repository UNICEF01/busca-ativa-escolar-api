<?php
/**
 * busca-ativa-escolar-api
 * CaseTransformer.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 07/01/2017, 23:46
 */

namespace BuscaAtivaEscolar\Transformers;


use BuscaAtivaEscolar\ChildCase;
use League\Fractal\TransformerAbstract;

class CaseTransformer extends TransformerAbstract {

	protected $availableIncludes = [
		'steps',
		'child',
		'assignedUser',
		'currentStep',
	];

	protected $defaultIncludes = [
		'steps',
		'currentStep',
	];

	public function transform(ChildCase $case) {
		return [
			'id' => $case->id,
			'child_id' => $case->child_id,

			'case_status' => $case->case_status,

			'name' => $case->name,

			'risk_level' => $case->risk_level,

			'is_current' => $case->is_current,

			'assigned_group_id' => $case->assigned_group_id,
			'assigned_user_id' => $case->assigned_user_id,

			'alert_cause_id' => $case->alert_cause_id,
			'case_cause_ids' => $case->case_cause_ids,

			'created_by_user_id' => $case->created_by_user_id,

			'current_step_id' => $case->current_step_id,
			'current_step_type' => $case->current_step_type,

			'created_at' => $case->created_at ? $case->created_at->toIso8601String() : null,
			'updated_at' => $case->updated_at ? $case->updated_at->toIso8601String() : null,
			'enrolled_at' => $case->enrolled_at,
            'tenant_id' => $case->tenant ? $case->tenant->id : null,
            'group' => $case->group ? $case->group : null
		];
	}

	public function includeCurrentStep(ChildCase $case) {
		$currentStep = $case->currentStep;
		return $this->item($currentStep, new StepTransformer(), false);
	}

	public function includeSteps(ChildCase $case) {
		$steps = $case->fetchSteps();
		return $this->collection($steps, new StepTransformer(), false);
	}

	public function includeChild(ChildCase $case) {
		$child = $case->child;
		return $this->item($child, new ChildTransformer, false);
	}

	public function includeAssignedUser(ChildCase $case) {
		$assignedUser = $case->assignedUser;
		return $this->item($assignedUser, new UserTransformer, false);
	}

}