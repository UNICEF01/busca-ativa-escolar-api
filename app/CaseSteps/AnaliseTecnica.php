<?php
/**
 * busca-ativa-escolar-api
 * AnaliseTecnica.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 29/12/2016, 14:10
 */

namespace BuscaAtivaEscolar\CaseSteps;

use BuscaAtivaEscolar\Traits\Data\checkPhases;
use BuscaAtivaEscolar\User;
use Illuminate\Database\Eloquent\Builder;

class AnaliseTecnica extends CaseStep {

	protected $table = "case_steps_analise_tecnica";

	public $stepFields = [
		'analysis_details'
	];

	protected function onStart($prevStep = null) {
		if(!$prevStep || !$prevStep->assignedUser) return $this->flagAsPendingAssignment();
		$this->assignToUser($prevStep->assignedUser);
	}

	public function validate($data, $isCompletingStep = false) {
		return validator($data, [
			'analysis_details' => 'required|string',
		]);
	}

	public function applyAssignableUsersFilter(Builder $query) {
		return $query->whereIn('type', [User::TYPE_TECNICO_VERIFICADOR, User::TYPE_SUPERVISOR_INSTITUCIONAL, User::TYPE_GESTOR_OPERACIONAL]);
	}
    use checkPhases;
}