<?php
/**
 * busca-ativa-escolar-api
 * Rematricula.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 29/12/2016, 14:12
 */

namespace BuscaAtivaEscolar\CaseSteps;

use BuscaAtivaEscolar\Traits\Data\checkPhases;
use BuscaAtivaEscolar\User;
use Illuminate\Database\Eloquent\Builder;

class Rematricula extends CaseStep {

	protected $table = 'case_steps_rematricula';

	public $stepFields = [
		'reinsertion_date',
		'reinsertion_grade',

		'school_id',
		'school_name',
		//'school_censo_id', // school_id is now school_censo_id
		'school_address',
		'school_cep',
		'school_neighborhood',
		'school_city_id',
		'school_city_name',
		'school_uf',
		'school_contact_name',
		'school_contact_email',
		'school_contact_position',
		'school_phone',
		'school_email',

		'observations',
	];

	protected function onStart($prevStep = null) {
		$this->flagAsPendingAssignment();
	}

	protected function onComplete() : bool {
		$this->childCase->enrolled_at = $this->reinsertion_date;
		$this->childCase->save();

		return true;
	}

	public function applyAssignableUsersFilter(Builder $query) {
		return $query->whereIn('type', [
			User::TYPE_SUPERVISOR_INSTITUCIONAL,
			User::TYPE_GESTOR_OPERACIONAL,
			User::TYPE_SUPERVISOR_ESTADUAL,
            User::TYPE_COORDENADOR_ESTADUAL
		]);
	}
    /**
     * Cases belonging to this child
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->hasMany('BuscaAtivaEscolar\ChildCase', 'child_id', 'child_id');
    }

    public function validate($data, $isCompletingStep = false) {
		$data['is_completing_step'] = $isCompletingStep;

		return validator($data, [
			'reinsertion_date' => 'required_for_completion|date',
			'reinsertion_grade' => 'required_for_completion|' . \BuscaAtivaEscolar\Data\SchoolGrade::getSlugValidationMask(),

			'school_id' => 'required_for_completion|string',
			'school_name' => 'required_for_completion|string',
			//'school_censo_id' => 'nullable|string',
			'school_address' => 'required_for_completion|string',
			'school_cep' => 'nullable|digits:8',
			'school_neighborhood' => 'nullable|string',
			'school_city_id' => 'required_for_completion|string',
			'school_city_name' => 'required_for_completion|string',
			'school_uf' => 'required_for_completion|string|size:2',
			'school_contact_name' => 'required_for_completion|string',
			'school_contact_email' => 'nullable|email',
			'school_contact_position' => 'nullable|string',
			'school_phone' => 'required_for_completion|alpha_dash',
			'school_email' => 'nullable|email',

			'observations' => 'nullable|string',
		]);
	}

    use checkPhases;
}